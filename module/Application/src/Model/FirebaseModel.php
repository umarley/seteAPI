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

    private function getDocumentosConfig() {
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

}
