<?php

namespace App\Models\Egap\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BemImovel extends Model
{
    protected $connection = 'egap';
    protected $table = 'imo_imovel';
    protected $primaryKey = 'Id'; 
    public $timestamps = false;
    protected $guarded = [];
    
    public function setoresRelacaoRef()
    {
        return $this->belongsTo(\App\Models\Egap\Cadastro\Setores::class, 'Id_setores', 'id');
    }

    public function responsavelRelacaoRef()
    {
        return $this->belongsTo(Responsavel::class, 'id_responsavel', 'id');
    }

    public function processoAdmRelacaoRef()
    {
        return $this->belongsTo(Processo::class, 'num_processo_adm', 'id');
    }

    public function tipoImovelRelacaoRef()
    {
        return $this->belongsTo(TipoImovel::class, 'Id_tipoimovel', 'Id');
    }

    public function denominacaoRelacaoRef()
    {
        return $this->belongsTo(Denominacao::class, 'id_denominacao', 'id');
    }

    public function tipoDeBemRelacaoRef()
    {
        return $this->belongsTo(TipoDeBem::class, 'Id_tipodebem', 'Id');
    }

    public function planoContasRelacaoRef()
    {
        return $this->belongsTo(\App\Models\Egap\Cadastro\ContaContabil::class, 'id_planocontas', 'id');
    }

    public function elementoDespesaRelacaoRef()
    {
        return $this->belongsTo(\App\Models\Egap\Cadastro\ElementoDespesa::class, 'id_elementodespesa', 'id');
    }
    
    public function cidadeRelacaoRef()
    {
        return $this->belongsTo(Cidades::class, 'id_cidade', 'Id'); 
    }

    public function cidufRelacaoRef()
    {
        return $this->belongsTo(CidUf::class, 'id_ciduf', 'id');
    }

    public function situacaoRelacaoRef()
    {
        return $this->belongsTo(Situacao::class, 'Id_situacao', 'Id');
    }

    public function condicaoUsoRelacaoRef()
    {
        return $this->belongsTo(CondicaoUso::class, 'id_condicaouso', 'Id');
    }

    public function estadoConservacaoRelacaoRef()
    {
        return $this->belongsTo(EstadoConservacao::class, 'Id_estadoconservacao', 'Id');
    }

    public function entradaSaidaRelacaoRef()
    {
        return $this->belongsTo(EntradaSaida::class, 'id_entradasaida', 'Id');
    }

    public function tributosRelacaoRef()
    {
        return $this->hasMany(Tributo::class, 'Id_imovel', 'Id');
    }

    public function cedidosRelacaoRef()
    {
        return $this->hasMany(\App\Models\Egap\Patrimonio\BensImoveis\Cedido::class, 'id_imovel', 'Id');
    }

    public function reavaliacoesRelacaoRef()
    {
        return $this->hasMany(\App\Models\Egap\Patrimonio\BensImoveis\Reavaliacao::class, 'Id_imovel', 'Id');
    }

    public function obrasRelacaoRef()
    {
        return $this->hasMany(\App\Models\Egap\Patrimonio\BensImoveis\Obra::class, 'id_imovel', 'Id');
    }
}