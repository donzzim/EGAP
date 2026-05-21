-- Migra usuarios de jos_users/mat_infousers para users.
--
-- Regra principal:
-- - considera que mat_infousers.usuario_id referencia jos_users.id;
-- - compara usuarios existentes em users pelo CPF sem mascara;
-- - em duplicidades do legado, usa somente o primeiro registro por CPF
--   conforme menor jos_users.id e menor mat_infousers.id;
-- - evita inserir registros que quebrariam os indices unicos de users
--   em login, email ou cpf;
-- - mapeia jos_users.block para users.ativo:
--   block = 0 => ativo = 1
--   block = 1 => ativo = 0
--
-- Execute este arquivo inteiro na mesma conexao/sessao MySQL.

USE patrimonio;

DROP TEMPORARY TABLE IF EXISTS tmp_users_cpf;
CREATE TEMPORARY TABLE tmp_users_cpf AS
SELECT
    id,
    NULLIF(REPLACE(REPLACE(TRIM(cpf), '.', ''), '-', ''), '') AS cpf_norm
FROM users
WHERE cpf IS NOT NULL;

CREATE INDEX idx_tmp_users_cpf ON tmp_users_cpf (cpf_norm);

DROP TEMPORARY TABLE IF EXISTS tmp_users_login;
CREATE TEMPORARY TABLE tmp_users_login AS
SELECT id, login
FROM users;

CREATE INDEX idx_tmp_users_login ON tmp_users_login (login);

DROP TEMPORARY TABLE IF EXISTS tmp_users_email;
CREATE TEMPORARY TABLE tmp_users_email AS
SELECT id, email
FROM users;

CREATE INDEX idx_tmp_users_email ON tmp_users_email (email);

DROP TEMPORARY TABLE IF EXISTS tmp_jos_candidates;
CREATE TEMPORARY TABLE tmp_jos_candidates AS
SELECT
    jos_user_id,
    mat_infouser_id,
    login,
    name,
    email,
    password,
    block,
    cpf,
    matricula
FROM (
    SELECT
        ju.id AS jos_user_id,
        mi.id AS mat_infouser_id,
        ju.username AS login,
        ju.name,
        ju.email,
        ju.password,
        ju.block,
        NULLIF(REPLACE(REPLACE(TRIM(mi.cpf), '.', ''), '-', ''), '') AS cpf,
        NULLIF(REPLACE(REPLACE(TRIM(mi.matricula), '.', ''), '-', ''), '') AS matricula,
        ROW_NUMBER() OVER (
            PARTITION BY NULLIF(REPLACE(REPLACE(TRIM(mi.cpf), '.', ''), '-', ''), '')
            ORDER BY ju.id ASC, mi.id ASC
        ) AS rn_cpf
    FROM jos_users ju
    INNER JOIN mat_infousers mi ON mi.usuario_id = ju.id
    WHERE NULLIF(REPLACE(REPLACE(TRIM(mi.cpf), '.', ''), '-', ''), '') IS NOT NULL
) ranked
WHERE rn_cpf = 1;

CREATE INDEX idx_tmp_jos_candidates_cpf ON tmp_jos_candidates (cpf);
CREATE INDEX idx_tmp_jos_candidates_login ON tmp_jos_candidates (login);
CREATE INDEX idx_tmp_jos_candidates_email ON tmp_jos_candidates (email);

-- Tambem resolve duplicidades de login/email dentro do proprio lote.
-- Isso evita erro de chave unica no INSERT quando dois CPFs diferentes
-- apontam para o mesmo username/email no legado.
DROP TEMPORARY TABLE IF EXISTS tmp_first_login;
CREATE TEMPORARY TABLE tmp_first_login AS
SELECT
    login,
    MIN(jos_user_id) AS keep_jos_user_id
FROM tmp_jos_candidates
WHERE login IS NOT NULL
  AND TRIM(login) <> ''
GROUP BY login;

CREATE INDEX idx_tmp_first_login ON tmp_first_login (login, keep_jos_user_id);

DROP TEMPORARY TABLE IF EXISTS tmp_first_email;
CREATE TEMPORARY TABLE tmp_first_email AS
SELECT
    email,
    MIN(jos_user_id) AS keep_jos_user_id
FROM tmp_jos_candidates
WHERE email IS NOT NULL
  AND TRIM(email) <> ''
GROUP BY email;

CREATE INDEX idx_tmp_first_email ON tmp_first_email (email, keep_jos_user_id);

DROP TEMPORARY TABLE IF EXISTS tmp_safe_insert_users;
CREATE TEMPORARY TABLE tmp_safe_insert_users AS
SELECT c.*
FROM tmp_jos_candidates c
INNER JOIN tmp_first_login fl
    ON fl.login = c.login
   AND fl.keep_jos_user_id = c.jos_user_id
INNER JOIN tmp_first_email fe
    ON fe.email = c.email
   AND fe.keep_jos_user_id = c.jos_user_id
LEFT JOIN tmp_users_cpf uc ON uc.cpf_norm = c.cpf
LEFT JOIN tmp_users_login ul ON ul.login = c.login
LEFT JOIN tmp_users_email ue ON ue.email = c.email
WHERE uc.id IS NULL
  AND ul.id IS NULL
  AND ue.id IS NULL
  AND c.login IS NOT NULL
  AND TRIM(c.login) <> ''
  AND c.email IS NOT NULL
  AND TRIM(c.email) <> '';

-- Conferencias antes da insercao.
SELECT COUNT(*) AS candidatos_legacy_primeiro_por_cpf
FROM tmp_jos_candidates;

SELECT COUNT(*) AS candidatos_para_inserir
FROM tmp_safe_insert_users;

SELECT
    jos_user_id,
    mat_infouser_id,
    login,
    name,
    email,
    cpf,
    matricula,
    block,
    CASE
        WHEN block = 0 THEN 1
        WHEN block = 1 THEN 0
        ELSE 1
    END AS ativo_calculado
FROM tmp_safe_insert_users
ORDER BY jos_user_id
LIMIT 20;

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
    CASE
        WHEN block = 0 THEN 1
        WHEN block = 1 THEN 0
        ELSE 1
    END AS ativo,
    NULL AS moodle_id,
    NULL AS email_verified_at,
    password,
    NULL AS remember_token,
    NOW() AS created_at,
    NOW() AS updated_at
FROM tmp_safe_insert_users
ORDER BY jos_user_id;

SELECT ROW_COUNT() AS usuarios_inseridos;

COMMIT;

-- Validacao apos commit.
SELECT COUNT(*) AS total_users_apos_insert
FROM users;

SELECT COUNT(*) AS usuarios_criados_nesta_execucao
FROM users
WHERE created_at >= NOW() - INTERVAL 5 MINUTE;
