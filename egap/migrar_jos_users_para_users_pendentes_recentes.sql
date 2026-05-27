-- Migração de usuários pendentes de jos_users/mat_infousers para users.
--
-- Requisitos:
-- - MySQL 8+ (usa ROW_NUMBER).
-- - Execute o arquivo inteiro na mesma sessão, pois usa tabelas temporárias.
--
-- Regras desta rodada:
-- 1. mat_infousers.usuario_id referencia jos_users.id.
-- 2. Para cada jos_users.id, usa o registro mais recente de mat_infousers
--    (date_time DESC, id DESC).
-- 3. Para CPF repetido no legado, elege somente um vencedor, priorizando:
--    email preenchido, usurious desbloqueado, matricula preenchida,
--    informação mais recente e maior id como desempate.
-- 4. CPF e comparado/inserido somente com dígitos.
-- 5. Matrícula e inserida sem "." ou "-".
-- 6. Usuário cujo CPF ja exista em users nao e reinserido.
-- 7. Colisões de login/email com users ou dentro do novo lote nao sao
--    inseridas; aparecem nos relatórios para correção posterior.
-- 8. jos_users.block = 0 vira users.ativo = 1.
--    jos_users.block = 1 vira users.ativo = 0.
--
-- Atenção:
-- - O valor de password e copiado do legado sem conversão.
-- - Para apenas auditar, execute até a secao "PREVIEW E RELATORIOS" e pare
--   antes de START TRANSACTION.

USE patrimonio;

-- Chaves já ocupadas em users. Tabelas separadas evitam a limitação do
-- MySQL de reabrir a mesma tabela temporária varias vezes numa consulta.
DROP TEMPORARY TABLE IF EXISTS tmp_mig_users_cpf;
CREATE TEMPORARY TABLE tmp_mig_users_cpf AS
SELECT
    id,
    CAST(NULLIF(REGEXP_REPLACE(TRIM(cpf), '[^0-9]', ''), '') AS CHAR(11)) AS cpf_norm
FROM users
WHERE CAST(NULLIF(REGEXP_REPLACE(TRIM(cpf), '[^0-9]', ''), '') AS CHAR(11)) IS NOT NULL;

CREATE INDEX idx_tmp_mig_users_cpf ON tmp_mig_users_cpf (cpf_norm);

DROP TEMPORARY TABLE IF EXISTS tmp_mig_users_login;
CREATE TEMPORARY TABLE tmp_mig_users_login AS
SELECT id, login
FROM users;

CREATE INDEX idx_tmp_mig_users_login ON tmp_mig_users_login (login);

DROP TEMPORARY TABLE IF EXISTS tmp_mig_users_email;
CREATE TEMPORARY TABLE tmp_mig_users_email AS
SELECT id, email
FROM users;

CREATE INDEX idx_tmp_mig_users_email ON tmp_mig_users_email (email);

-- Registro mais recente de informações complementares para cada usuario.
DROP TEMPORARY TABLE IF EXISTS tmp_mig_latest_info;
CREATE TEMPORARY TABLE tmp_mig_latest_info AS
SELECT
    mat_infouser_id,
    usuario_id,
    info_date_time,
    cpf,
    matricula
FROM (
    SELECT
        mi.id AS mat_infouser_id,
        mi.usuario_id,
        mi.date_time AS info_date_time,
        CAST(NULLIF(REGEXP_REPLACE(TRIM(mi.cpf), '[^0-9]', ''), '') AS CHAR(11)) AS cpf,
        NULLIF(REPLACE(REPLACE(TRIM(mi.matricula), '.', ''), '-', ''), '') AS matricula,
        ROW_NUMBER() OVER (
            PARTITION BY mi.usuario_id
            ORDER BY (mi.date_time IS NULL) ASC, mi.date_time DESC, mi.id DESC
        ) AS rn_info
    FROM mat_infousers mi
    INNER JOIN jos_users ju ON ju.id = mi.usuario_id
) ranked_info
WHERE rn_info = 1;

CREATE INDEX idx_tmp_mig_latest_info_usuario ON tmp_mig_latest_info (usuario_id);
CREATE INDEX idx_tmp_mig_latest_info_cpf ON tmp_mig_latest_info (cpf);

-- Eleicao de um unico cadastro legado por CPF.
DROP TEMPORARY TABLE IF EXISTS tmp_mig_cpf_ranked;
CREATE TEMPORARY TABLE tmp_mig_cpf_ranked AS
SELECT
    ju.id AS jos_user_id,
    li.mat_infouser_id,
    li.info_date_time,
    ju.username AS login,
    ju.name,
    ju.email,
    ju.password,
    ju.block,
    li.cpf,
    li.matricula,
    ROW_NUMBER() OVER (
        PARTITION BY li.cpf
        ORDER BY
            (NULLIF(TRIM(ju.email), '') IS NOT NULL) DESC,
            (ju.block = 0) DESC,
            (li.matricula IS NOT NULL) DESC,
            (li.info_date_time IS NULL) ASC,
            li.info_date_time DESC,
            li.mat_infouser_id DESC,
            ju.id DESC
    ) AS rn_cpf
FROM jos_users ju
INNER JOIN tmp_mig_latest_info li ON li.usuario_id = ju.id
WHERE li.cpf IS NOT NULL;

CREATE INDEX idx_tmp_mig_cpf_ranked_cpf ON tmp_mig_cpf_ranked (cpf);

DROP TEMPORARY TABLE IF EXISTS tmp_mig_cpf_winners;
CREATE TEMPORARY TABLE tmp_mig_cpf_winners AS
SELECT
    jos_user_id,
    mat_infouser_id,
    info_date_time,
    login,
    name,
    email,
    password,
    block,
    cpf,
    matricula
FROM tmp_mig_cpf_ranked
WHERE rn_cpf = 1;

CREATE INDEX idx_tmp_mig_cpf_winners_cpf ON tmp_mig_cpf_winners (cpf);
CREATE INDEX idx_tmp_mig_cpf_winners_login ON tmp_mig_cpf_winners (login);
CREATE INDEX idx_tmp_mig_cpf_winners_email ON tmp_mig_cpf_winners (email);

-- Mantem somente CPFs ainda inexistentes em users.
DROP TEMPORARY TABLE IF EXISTS tmp_mig_missing_by_cpf;
CREATE TEMPORARY TABLE tmp_mig_missing_by_cpf AS
SELECT c.*
FROM tmp_mig_cpf_winners c
LEFT JOIN tmp_mig_users_cpf u ON u.cpf_norm = c.cpf
WHERE u.id IS NULL;

CREATE INDEX idx_tmp_mig_missing_cpf ON tmp_mig_missing_by_cpf (cpf);
CREATE INDEX idx_tmp_mig_missing_login ON tmp_mig_missing_by_cpf (login);
CREATE INDEX idx_tmp_mig_missing_email ON tmp_mig_missing_by_cpf (email);

-- Identifica conflitos com chaves unicas ja existentes em users.
DROP TEMPORARY TABLE IF EXISTS tmp_mig_missing_flags;
CREATE TEMPORARY TABLE tmp_mig_missing_flags AS
SELECT
    c.*,
    ul.id AS conflicting_user_login_id,
    ue.id AS conflicting_user_email_id
FROM tmp_mig_missing_by_cpf c
LEFT JOIN tmp_mig_users_login ul ON ul.login = c.login
LEFT JOIN tmp_mig_users_email ue ON ue.email = c.email;

CREATE INDEX idx_tmp_mig_flags_login ON tmp_mig_missing_flags (login);
CREATE INDEX idx_tmp_mig_flags_email ON tmp_mig_missing_flags (email);

-- Candidatos sem conflito com users que ainda precisam competir entre si
-- quando compartilham login ou email.
DROP TEMPORARY TABLE IF EXISTS tmp_mig_batch_ranked;
CREATE TEMPORARY TABLE tmp_mig_batch_ranked AS
SELECT
    c.*,
    ROW_NUMBER() OVER (
        PARTITION BY c.login
        ORDER BY
            (c.block = 0) DESC,
            (c.matricula IS NOT NULL) DESC,
            (c.info_date_time IS NULL) ASC,
            c.info_date_time DESC,
            c.mat_infouser_id DESC,
            c.jos_user_id DESC
    ) AS rn_login,
    ROW_NUMBER() OVER (
        PARTITION BY c.email
        ORDER BY
            (c.block = 0) DESC,
            (c.matricula IS NOT NULL) DESC,
            (c.info_date_time IS NULL) ASC,
            c.info_date_time DESC,
            c.mat_infouser_id DESC,
            c.jos_user_id DESC
    ) AS rn_email
FROM tmp_mig_missing_flags c
WHERE c.conflicting_user_login_id IS NULL
  AND c.conflicting_user_email_id IS NULL
  AND c.login IS NOT NULL
  AND TRIM(c.login) <> ''
  AND c.email IS NOT NULL
  AND TRIM(c.email) <> '';

CREATE INDEX idx_tmp_mig_batch_cpf ON tmp_mig_batch_ranked (cpf);
CREATE INDEX idx_tmp_mig_batch_login ON tmp_mig_batch_ranked (login);
CREATE INDEX idx_tmp_mig_batch_email ON tmp_mig_batch_ranked (email);

DROP TEMPORARY TABLE IF EXISTS tmp_mig_to_insert;
CREATE TEMPORARY TABLE tmp_mig_to_insert AS
SELECT
    jos_user_id,
    mat_infouser_id,
    info_date_time,
    login,
    name,
    email,
    password,
    block,
    cpf,
    matricula
FROM tmp_mig_batch_ranked
WHERE rn_login = 1
  AND rn_email = 1;

CREATE INDEX idx_tmp_mig_to_insert_cpf ON tmp_mig_to_insert (cpf);

-- PREVIEW E RELATORIOS
-- Pare aqui antes do START TRANSACTION caso queira apenas auditar.

SELECT COUNT(*) AS usuarios_com_info_mais_recente
FROM tmp_mig_latest_info;

SELECT COUNT(*) AS cpfs_unicos_vencedores_no_legado
FROM tmp_mig_cpf_winners;

SELECT COUNT(*) AS cpfs_ainda_ausentes_em_users
FROM tmp_mig_missing_by_cpf;

SELECT COUNT(*) AS candidatos_para_inserir_nesta_rodada
FROM tmp_mig_to_insert;

SELECT
    'login_ja_existente_em_users' AS motivo,
    COUNT(*) AS quantidade
FROM tmp_mig_missing_flags
WHERE conflicting_user_login_id IS NOT NULL;

SELECT
    'email_ja_existente_em_users' AS motivo,
    COUNT(*) AS quantidade
FROM tmp_mig_missing_flags
WHERE conflicting_user_email_id IS NOT NULL;

SELECT
    'login_vazio' AS motivo,
    COUNT(*) AS quantidade
FROM tmp_mig_missing_flags
WHERE login IS NULL OR TRIM(login) = '';

SELECT
    'email_vazio' AS motivo,
    COUNT(*) AS quantidade
FROM tmp_mig_missing_flags
WHERE email IS NULL OR TRIM(email) = '';

SELECT
    'duplicidade_login_no_novo_lote' AS motivo,
    COUNT(*) AS quantidade
FROM tmp_mig_batch_ranked
WHERE rn_login > 1;

SELECT
    'duplicidade_email_no_novo_lote' AS motivo,
    COUNT(*) AS quantidade
FROM tmp_mig_batch_ranked
WHERE rn_email > 1;

-- Amostra dos registros que serao inseridos.
SELECT
    jos_user_id,
    mat_infouser_id,
    info_date_time,
    login,
    name,
    email,
    cpf,
    matricula,
    block,
    CASE WHEN block = 0 THEN 1 ELSE 0 END AS ativo
FROM tmp_mig_to_insert
ORDER BY info_date_time DESC, jos_user_id DESC
LIMIT 30;

-- Registros ausentes por CPF que nao foram liberados automaticamente.
SELECT
    jos_user_id,
    mat_infouser_id,
    login,
    email,
    cpf,
    block,
    conflicting_user_login_id,
    conflicting_user_email_id,
    CASE
        WHEN login IS NULL OR TRIM(login) = '' THEN 'login_vazio'
        WHEN email IS NULL OR TRIM(email) = '' THEN 'email_vazio'
        WHEN conflicting_user_login_id IS NOT NULL THEN 'login_ja_existente_em_users'
        WHEN conflicting_user_email_id IS NOT NULL THEN 'email_ja_existente_em_users'
        ELSE 'verificar_duplicidade_interna_login_email'
    END AS motivo
FROM tmp_mig_missing_flags
WHERE login IS NULL
   OR TRIM(login) = ''
   OR email IS NULL
   OR TRIM(email) = ''
   OR conflicting_user_login_id IS NOT NULL
   OR conflicting_user_email_id IS NOT NULL
ORDER BY motivo, jos_user_id;

-- INSERT
SET @migration_started_at = NOW();

START TRANSACTION;

INSERT INTO users (
    login,
    name,
    email,
    avatar_url,
    cpf,
    telefone,
    matricula,
    numero_funcional,
    ativo,
    moodle_id,
    email_verified_at,
    password,
    remember_token,
    created_at,
    updated_at
)
SELECT
    login,
    name,
    email,
    NULL AS avatar_url,
    cpf,
    NULL AS telefone,
    matricula,
    NULL AS numero_funcional,
    CASE WHEN block = 0 THEN 1 ELSE 0 END AS ativo,
    NULL AS moodle_id,
    NULL AS email_verified_at,
    password,
    NULL AS remember_token,
    NOW() AS created_at,
    NOW() AS updated_at
FROM tmp_mig_to_insert
ORDER BY info_date_time DESC, jos_user_id DESC;

SET @usuarios_inseridos = ROW_COUNT();

COMMIT;

-- VALIDACAO POS-INSERT
SELECT @usuarios_inseridos AS usuarios_inseridos;

SELECT COUNT(*) AS novos_registros_localizados_por_cpf
FROM users u
INNER JOIN tmp_mig_to_insert c
    ON CAST(NULLIF(REGEXP_REPLACE(TRIM(u.cpf), '[^0-9]', ''), '') AS CHAR(11)) = c.cpf
WHERE u.created_at >= @migration_started_at;

SELECT COUNT(*) AS divergencias_block_ativo
FROM users u
INNER JOIN tmp_mig_to_insert c
    ON CAST(NULLIF(REGEXP_REPLACE(TRIM(u.cpf), '[^0-9]', ''), '') AS CHAR(11)) = c.cpf
WHERE u.created_at >= @migration_started_at
  AND u.ativo <> CASE WHEN c.block = 0 THEN 1 ELSE 0 END;

SELECT COUNT(*) AS cpfs_duplicados_em_users
FROM (
    SELECT CAST(NULLIF(REGEXP_REPLACE(TRIM(cpf), '[^0-9]', ''), '') AS CHAR(11)) AS cpf_norm
    FROM users
    WHERE CAST(NULLIF(REGEXP_REPLACE(TRIM(cpf), '[^0-9]', ''), '') AS CHAR(11)) IS NOT NULL
    GROUP BY cpf_norm
    HAVING COUNT(*) > 1
) duplicate_cpfs;

SELECT COUNT(*) AS logins_duplicados_em_users
FROM (
    SELECT login
    FROM users
    GROUP BY login
    HAVING COUNT(*) > 1
) duplicate_logins;

SELECT COUNT(*) AS emails_duplicados_em_users
FROM (
    SELECT email
    FROM users
    GROUP BY email
    HAVING COUNT(*) > 1
) duplicate_emails;
