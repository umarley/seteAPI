<?php

namespace Application\Model;

use Google\Cloud\Firestore\FirestoreClient;


class FirebaseModel {

    private $_db;

    public function __construct() {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../../../../config/autoload/google.local.json');

        $this->_db = new FirestoreClient([
            'projectId' => 'softwareter'
        ]);
    }

    public function procurarDocumentoUsuarioPorEmail($email) {
        $usersRef = $this->_db->collection('users');
        $query = $usersRef->where('EMAIL', '==', $email);
        $documents = $query->documents();
        $arDocumentos = [];
        foreach ($documents as $document) {
            if ($document->exists()) {
                $arDocumentos[$document->id()] = $document->data();
            }
        }
        return $arDocumentos;
    }

    public function getDocumentosConfig() {
        $configRefs = $this->_db->collection('config')->documents();
        $arLista = [];
        foreach ($configRefs as $row) {
            $arLista[] = $row->id();
        }
        return $arLista;
    }

    /**
     * Obtém os campos de um documento presente na coleção config, caso o documento não exista, retorna false 
     * se existir retorna os campos
     * @param String $doc
     */
    public function getDocumentoByIdConfig($doc) {
        $configRefs = $this->_db->collection('config')->document($doc);
        $snapshot = $configRefs->snapshot();
        if ($snapshot->exists()) {
            return $snapshot->data();
        } else {
            return false;
        }
    }

    public function setDocumentoColecaoConfig($documento, $arCampos) {
        $citiesRef = $this->_db->collection('config');
        $citiesRef->document($documento)->set($arCampos);
        return true;
    }

    private function getDocumentosMunicipios() {
        $configRefs = $this->_db->collection('municipios')->documents();
        $arLista = [];
        foreach ($configRefs as $row) {
            $arLista[] = $row->id();
        }
        return $arLista;
    }
    
    public function getUsersFirebase() {
        $configRefs = $this->_db->collection('users')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    public function getAlunosMunicipio($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('alunos')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    public function getEscolasMunicipio($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('escolas')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    public function getEscolasTemAluno($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('escolatemalunos')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    public function getGaragens($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('garagem')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    public function getMotoristas($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('motoristas')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    public function getRotasMunicipio($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('rotas')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    public function getVeiculosMunicipio($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('veiculos')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    public function getRotasAtendeAlunoMunicipio($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('rotaatendealuno')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    public function getRotasDirigidaPorMotoristaMunicipio($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('rotadirigidapormotorista')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }
    
    public function getRotasPassaPorEscolaMunicipio($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('rotapassaporescolas')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }
    
        public function getRotasPossuiVeiculoMunicipio($codigoMunicipio) {
        $configRefs = $this->_db->collection('municipios')->document($codigoMunicipio)
                        ->collection('rotapossuiveiculo')->documents();
        $arLista = [];
        $auxiliar = 0;
        foreach ($configRefs as $row) {
            $arLista[$auxiliar] = $row->data();
            $arLista[$auxiliar]['id_firebase'] = $row->id();
            $auxiliar++;
        }
        return $arLista;
    }

    /**
     * Busca os documentos na coleção config e os insere na tabela firebase_config 
     * @return Array com a lista de documentos dentro da coleção config
     */
    public function processarDocumentosConfig() {
        $dbSeteFirebaseConfig = new \Db\Sete\FirebaseConfig();
        $arDocumentos = $this->getDocumentosConfig();
        $dbSeteFirebaseConfig->_truncate();
        foreach ($arDocumentos as $row) {
            $dbSeteFirebaseConfig->_inserir(['codigo_municipio' => $row]);
        }
        return $arDocumentos;
    }

    /**
     * Busca os documentos na coleção municipios do firebase e os insere na tabela firebase_municipios
     * @return Array com a lista de documentos dentro da coleção municipios
     */
    public function processarDocumentosMunicipios() {
        $dbSeteFirebaseMunicipios = new \Db\Sete\FirebaseMunicipios();
        $arDocumentos = $this->getDocumentosMunicipios();
        $dbSeteFirebaseMunicipios->_truncate();
        foreach ($arDocumentos as $row) {
            $dbSeteFirebaseMunicipios->_inserir(['codigo_municipio' => $row]);
        }
        return $arDocumentos;
    }
    
    public function excluirDocumentoUsuarioPorUID($uid) {
        $this->_db->collection('cities')->document($uid)->delete();
    }

}
