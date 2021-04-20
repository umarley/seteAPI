<?php

date_default_timezone_set('America/Sao_Paulo');
ignore_user_abort(true);

//If set to zero, no time limit is imposed.
set_time_limit(0);

use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Loader\StandardAutoloader;

/* /**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (is_string($path) && __FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Composer autoloading
include __DIR__ . '/../vendor/autoload.php';

if (!class_exists(Application::class)) {
    throw new RuntimeException(
                    "Unable to load application.\n"
                    . "- Type `composer install` if you are developing locally.\n"
                    . "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n"
                    . "- Type `docker-compose run laminas composer install` if you are using Docker.\n"
    );
}

// This example assumes the StandardAutoloader is autoloadable.
$loader = new StandardAutoloader();
// Register the "Phly" namespace:
$loader->registerNamespace('Db', __DIR__ . '/../module/Application/src/Entity');
$loader->register();

define('AMBIENTE_PRODUCAO', 'producao');
define('AMBIENTE_DEVELOPER', 'developer');

if (@$_SERVER['SERVER_ADDR'] !== '10.10.50.41') {
    define('AMBIENTE_EXEC', AMBIENTE_DEVELOPER);
} else {
    define('AMBIENTE_EXEC', AMBIENTE_PRODUCAO);
}

//Início do serviço
sleep(60);
mainLoadData();

function mainLoadData() {
    $dbCoreCargaDados = new \Db\Core\CargaDados();
    if ($dbCoreCargaDados->podeExecutarCargaDados($dbCoreCargaDados::CARGA_MUNICIPIOS)) {
        cargaMunicipios();
        $dbCoreCargaDados->_atualizar($dbCoreCargaDados::CARGA_MUNICIPIOS, ['data_carga' => date('Y-m-d H:i:s')]);
    }
    if ($dbCoreCargaDados->podeExecutarCargaDados($dbCoreCargaDados::CARGA_USERS)) {
        cargaUsers();
        $dbCoreCargaDados->_atualizar($dbCoreCargaDados::CARGA_USERS, ['data_carga' => date('Y-m-d H:i:s')]);
    }

    cargaUsuariosLiberados();
}

function cargaUsuariosLiberados() {
    $modelFirebase = new \Application\Model\FirebaseModel();
    $arMunicipiosConfig = $modelFirebase->getDocumentosConfig();
    $dbSeteUsuariosLiberados = new \Db\Sete\SeteUsuariosLiberados();
    $dbSeteUsuariosLiberados->_truncate();
    foreach ($arMunicipiosConfig as $row) {
        $arPermissao = $modelFirebase->getDocumentoByIdConfig($row);
        foreach ($arPermissao['users'] as $rowUid) {
            $dbSeteUsuariosLiberados->_inserir(['uid' => $rowUid, 'type' => 'users']);
        }
        foreach ($arPermissao['readers'] as $rowUid) {
            $dbSeteUsuariosLiberados->_inserir(['uid' => $rowUid, 'type' => 'readers']);
        }
        foreach ($arPermissao['admin'] as $rowUid) {
            $dbSeteUsuariosLiberados->_inserir(['uid' => $rowUid, 'type' => 'admin']);
        }
    }
}

function cargaUsers() {
    $modelFirebase = new \Application\Model\FirebaseModel();
    $dbSeteUsuarios = new \Db\Sete\SeteUsuarios();
    $arUsers = $modelFirebase->getUsersFirebase();
    foreach ($arUsers as $rowUser) {
        $arUsersBD = deParaUsers($rowUser);
        $usuarioExiste = $dbSeteUsuarios->usuarioExiste($rowUser['ID']);
        if ($usuarioExiste) {
            $arUsersBD['dt_alteracao'] = date("Y-m-d H:i:s");
            $dbSeteUsuarios->_atualizar($rowUser['ID'], $arUsersBD);
        } else {
            $arUsersBD['dt_criacao'] = date("Y-m-d H:i:s");
            $dbSeteUsuarios->_inserir($arUsersBD);
        }
    }
}

function cargaMunicipios() {
    echo "Populando lista de municipios...\r\n";
    populaListaMunicipios();
    $arMunicipios = listarMunicipios();
    processarMunicipios($arMunicipios);
}

function processarMunicipios($arMunicipios) {
    $modelFirebase = new \Application\Model\FirebaseModel();
    echo "Limpando base de dados...\r\n";
    limparBD();
    echo "Iniciando processo...\r\n";
    foreach ($arMunicipios as $row) {
        echo "Processar municipio {$row['codigo_municipio']} - " . strtoupper($row['nome_cidade']). "...\r\n";
        $arAlunos = $modelFirebase->getAlunosMunicipio($row['codigo_municipio']);
        echo "Processando dados alunos...\r\n";
        processarAlunos($row['codigo_municipio'], $arAlunos);
        $arEscolas = $modelFirebase->getEscolasMunicipio($row['codigo_municipio']);
        echo "Processando dados escolas...\r\n";
        processarEscolas($row['codigo_municipio'], $arEscolas);
        $arEscolaTemAluno = $modelFirebase->getEscolasTemAluno($row['codigo_municipio']);
        echo "Processando dados escola tem alunos...\r\n";
        processarEscolaTemAluno($row['codigo_municipio'], $arEscolaTemAluno);
        $arGaragens = $modelFirebase->getGaragens($row['codigo_municipio']);
        echo "Processando dados garagens...\r\n";
        processarGaragens($row['codigo_municipio'], $arGaragens);
        $arMotoristas = $modelFirebase->getMotoristas($row['codigo_municipio']);
        echo "Processando dados motoristas...\r\n";
        processarMotoristas($row['codigo_municipio'], $arMotoristas);
        $arRotas = $modelFirebase->getRotasMunicipio($row['codigo_municipio']);
        echo "Processando dados rotas...\r\n";
        processarRotas($row['codigo_municipio'], $arRotas);
        $arVeiculos = $modelFirebase->getVeiculosMunicipio($row['codigo_municipio']);
        echo "Processando dados veículos...\r\n";
        processarVeiculos($row['codigo_municipio'], $arVeiculos);
        $arRotasAtendeAluno = $modelFirebase->getRotasAtendeAlunoMunicipio($row['codigo_municipio']);
        echo "Processando dados rota atende aluno...\r\n";
        processarRotasAtendeAluno($row['codigo_municipio'], $arRotasAtendeAluno);
        $arRotasDirigidaPorMotorista = $modelFirebase->getRotasDirigidaPorMotoristaMunicipio($row['codigo_municipio']);
        echo "Processando dados rota dirigida por mororista...\r\n";
        processarRotasDirigidasPorMotorista($row['codigo_municipio'], $arRotasDirigidaPorMotorista);
        $arRotasPassaPorEscola = $modelFirebase->getRotasPassaPorEscolaMunicipio($row['codigo_municipio']);
        echo "Processando dados rota passa por escola...\r\n";
        processarRotasPassaPorEscola($row['codigo_municipio'], $arRotasPassaPorEscola);
        $arRotasPossuiVeiculo = $modelFirebase->getRotasPossuiVeiculoMunicipio($row['codigo_municipio']);
        echo "Processando dados rota possui veiculo...\r\n";
        processarRotasPossuiVeiculo($row['codigo_municipio'], $arRotasPossuiVeiculo);
    }
}

function limparBD() {
    $dbSeteRotaPossuiVeiculo = new \Db\Sete\SeteRotaPossuiVeiculo();
    $dbSeteRotaPassaPorEscola = new \Db\Sete\SeteRotaPassaPorEscola();
    $dbSeteRotaDirigidaPorMotorista = new \Db\Sete\SeteRotaDirigidaPorMotorista();
    $dbRotaAtendeAluno = new \Db\Sete\SeteRotaAtendeAluno();
    $dbSeteEscolaTemAluno = new \Db\Sete\SeteEscolaTemAlunos();
    $dbSeteAlunos = new \Db\Sete\SeteAlunos();
    $dbSeteRotas = new \Db\Sete\SeteRotas();
    $dbSeteVeiculos = new \Db\Sete\SeteVeiculos();
    $dbSeteEscolas = new \Db\Sete\SeteEscolas();
    $dbSeteGaragem = new \Db\Sete\SeteGaragem();
    $dbSeteMotoristas = new \Db\Sete\SeteMotoristas();
    $dbSeteRotaPossuiVeiculo->_deleteAll();
    $dbSeteRotaPassaPorEscola->_deleteAll();
    $dbSeteRotaDirigidaPorMotorista->_deleteAll();
    $dbRotaAtendeAluno->_deleteAll();
    $dbSeteEscolaTemAluno->_deleteAll();
    $dbSeteAlunos->_deleteAll();
    $dbSeteRotas->_deleteAll();
    $dbSeteVeiculos->_deleteAll();
    $dbSeteEscolas->_deleteAll();
    $dbSeteGaragem->_deleteAll();
    $dbSeteMotoristas->_deleteAll();
}

function processarRotasPossuiVeiculo($municipio, $arRotas) {
    $dbSeteRotaPossuiVeiculo = new \Db\Sete\SeteRotaPossuiVeiculo();
    $dbSeteVeiculo = new \Db\Sete\SeteVeiculos();
    $dbSeteRota = new \Db\Sete\SeteRotas();
    foreach ($arRotas as $rowRota) {
        $idVeiculo = $dbSeteVeiculo->getIdVeiculoByIdFirebase($municipio, $rowRota['ID_VEICULO']);
        $idRota = $dbSeteRota->getIdRotaByIdFirebase($municipio, $rowRota['ID_ROTA']);
        if (!empty($idVeiculo) && !empty($idRota)) {
            $arResult = $dbSeteRotaPossuiVeiculo->_inserir([
                'id_rota' => $idRota,
                'id_veiculo' => $idVeiculo,
                'codigo_cidade' => $municipio
            ]);
            if (!$arResult['result']) {
                echo $arResult['messages'] . "\r\n";
            }
        } else {
            echo "Não encontrado ID Veiculo ou ID Rota\r\n";
        }
    }
}

function processarRotasPassaPorEscola($municipio, $arRotas) {
    $dbSeteRotaPassaPorEscola = new \Db\Sete\SeteRotaPassaPorEscola();
    $dbSeteEscola = new \Db\Sete\SeteEscolas();
    $dbSeteRota = new \Db\Sete\SeteRotas();
    foreach ($arRotas as $rowRota) {
        $idEscola = $dbSeteEscola->getIdEscolaByIdFirebase($municipio, $rowRota['ID_ESCOLA']);
        $idRota = $dbSeteRota->getIdRotaByIdFirebase($municipio, $rowRota['ID_ROTA']);
        if (!empty($idEscola) && !empty($idRota)) {
            $arResult = $dbSeteRotaPassaPorEscola->_inserir([
                'id_rota' => $idRota,
                'id_escola' => $idEscola,
                'codigo_cidade' => $municipio
            ]);
            if (!$arResult['result']) {
                echo $arResult['messages'] . "\r\n";
            }
        } else {
            echo "Não encontrado ID Escola ou ID Rota\r\n";
        }
    }
}

function processarRotasDirigidasPorMotorista($municipio, $arRotasDirigidaMotorista) {
    $dbSeteRotaDirigidaPorMotorista = new \Db\Sete\SeteRotaDirigidaPorMotorista();
    $dbSeteMotorista = new \Db\Sete\SeteMotoristas();
    $dbSeteRota = new \Db\Sete\SeteRotas();
    foreach ($arRotasDirigidaMotorista as $rowRota) {
        $cpfMotorista = $dbSeteMotorista->getCPFByIdFirebase($municipio, $rowRota['CPF_MOTORISTA']);
        $idRota = $dbSeteRota->getIdRotaByIdFirebase($municipio, $rowRota['ID_ROTA']);
        if (!empty($cpfMotorista) && !empty($idRota)) {
            $arResult = $dbSeteRotaDirigidaPorMotorista->_inserir([
                'id_rota' => $idRota,
                'cpf_motorista' => $cpfMotorista,
                'codigo_cidade' => $municipio
            ]);
            if (!$arResult['result']) {
                echo $arResult['messages'] . "\r\n";
            }
        } else {
            echo "Não encontrado ID Aluno ou ID Rota\r\n";
        }
    }
}

function processarRotasAtendeAluno($municipio, $arRotasAtendeAluno) {
    $dbSeteRotaAtendeAluno = new \Db\Sete\SeteRotaAtendeAluno();
    $dbSeteAluno = new \Db\Sete\SeteAlunos();
    $dbSeteRota = new \Db\Sete\SeteRotas();
    foreach ($arRotasAtendeAluno as $rowRota) {
        $idAluno = $dbSeteAluno->getIdAlunoByFirebaseAndCodigoMunicipio($municipio, $rowRota['ID_ALUNO']);
        $idRota = $dbSeteRota->getIdRotaByIdFirebase($municipio, $rowRota['ID_ROTA']);
        if (!empty($idAluno) && !empty($idRota)) {
            $arResult = $dbSeteRotaAtendeAluno->_inserir([
                'id_rota' => $idRota,
                'id_aluno' => $idAluno,
                'codigo_cidade' => $municipio
            ]);
            if (!$arResult['result']) {
                echo $arResult['messages'] . "\r\n";
            }
        } else {
            echo "Não encontrado ID Aluno ou ID Rota\r\n";
        }
    }
}

function processarVeiculos($municipio, $arVeiculos) {
    $dbSeteVeiculos = new \Db\Sete\SeteVeiculos();
    foreach ($arVeiculos as $key => $rowVeiculo) {
        $arVeiculoDB = deParaVeiculos($key, $rowVeiculo);
        $arVeiculoDB['codigo_cidade'] = $municipio;
        $arResult = $dbSeteVeiculos->_inserir($arVeiculoDB);
        if (!$arResult['result']) {
            echo $arResult['messages'] . "\r\n";
        }
    }
}

function processarRotas($municipio, $arRotas) {
    $dbSeteRotas = new \Db\Sete\SeteRotas();
    foreach ($arRotas as $key => $rowRotas) {
        $arRotaDB = deParaRotas($key, $rowRotas);
        $arRotaDB['codigo_cidade'] = $municipio;
        $arResult = $dbSeteRotas->_inserir($arRotaDB);
        if (!$arResult['result']) {
            echo $arResult['messages'] . "\r\n";
        }
    }
}

function processarMotoristas($municipio, $arMotoristas) {
    $dbSeteMotorista = new \Db\Sete\SeteMotoristas();
    foreach ($arMotoristas as $key => $rowMotorista) {
        $arMotoristaDB = deParaMotoristas($key, $rowMotorista);
        $arMotoristaDB['codigo_cidade'] = $municipio;
        $arResult = $dbSeteMotorista->_inserir($arMotoristaDB);
        if (!$arResult['result']) {
            echo $arResult['messages'] . "\r\n";
        }
    }
}

function processarGaragens($municipio, $arGaragens) {
    $dbSeteGaragem = new \Db\Sete\SeteGaragem();
    foreach ($arGaragens as $key => $rowGaragem) {
        $arGaragemDB = deParaGaragem($key, $rowGaragem);
        $arGaragemDB['codigo_cidade'] = $municipio;
        $arResult = $dbSeteGaragem->_inserir($arGaragemDB);
        if (!$arResult['result']) {
            echo $arResult['messages'] . "\r\n";
        }
    }
}

function processarEscolas($municipio, $arEscolas) {
    $dbSeteEscolas = new \Db\Sete\SeteEscolas();
    foreach ($arEscolas as $key => $rowEscola) {
        $arEscolaDB = deParaEscolas($key, $rowEscola);
        $arEscolaDB['codigo_cidade'] = $municipio;
        $arResult = $dbSeteEscolas->_inserir($arEscolaDB);
        if (!$arResult['result']) {
            echo $arResult['messages'] . "\r\n";
        }
    }
}

function processarEscolaTemAluno($codigoMunicipio, $arEscolaTemAluno) {
    $dbSeteAlunos = new \Db\Sete\SeteAlunos();
    $dbSeteEscolaTemAluno = new \Db\Sete\SeteEscolaTemAlunos();
    foreach ($arEscolaTemAluno as $rowAlunoEscola) {
        $idFirebase = isset($rowAlunoEscola['ID_ALUNO']) ? $rowAlunoEscola['ID_ALUNO'] : $rowAlunoEscola['id_firebase'];
        $idAluno = $dbSeteAlunos->getIdAlunoByFirebaseAndCodigoMunicipio($codigoMunicipio, $idFirebase);
        if (!empty($idAluno)) {
            $dbSeteEscolaTemAluno->_inserir([
                'id_escola' => $rowAlunoEscola['ID_ESCOLA'],
                'id_aluno' => $idAluno,
                'codigo_cidade' => $codigoMunicipio
            ]);
        }
    }
}

function processarAlunos($municipio, $arAlunos) {
    $dbSeteAlunos = new \Db\Sete\SeteAlunos();
    foreach ($arAlunos as $key => $rowAluno) {
        $arAlunoDB = deParaAlunos($key, $rowAluno);
        $arAlunoDB['codigo_cidade'] = $municipio;
        $arResult = $dbSeteAlunos->_inserir($arAlunoDB);
        if (!$arResult['result']) {
            echo $arResult['messages'] . "\r\n";
        }
    }
}

function deParaVeiculos($key, $arVeiculo) {
    $arData['id_veiculo'] = $key;
    $arData['placa'] = $arVeiculo['PLACA'];
    $arData['modelo'] = $arVeiculo['MODELO'];
    $arData['ano'] = $arVeiculo['ANO'];
    $arData['modo'] = $arVeiculo['MODO'];
    $arData['origem'] = $arVeiculo['ORIGEM'];
    $arData['km_inicial'] = !empty($arVeiculo['KM_INICIAL']) ? formataDecimal($arVeiculo['KM_INICIAL']) : 0;
    $arData['capacidade'] = $arVeiculo['CAPACIDADE'];
    $arData['km_atual'] = !empty($arVeiculo['KM_ATUAL']) ? formataDecimal($arVeiculo['KM_ATUAL']) : 0;
    $arData['tipo'] = $arVeiculo['TIPO'];
    $arData['renavam'] = $arVeiculo['RENAVAM'];
    $arData['manutencao'] = $arVeiculo['MANUTENCAO'] ? 'S' : 'N';
    $arData['marca'] = $arVeiculo['MARCA'];
    $arData['id_firebase'] = $arVeiculo['id_firebase'];

    return $arData;
}

function deParaRotas($key, $arRotas) {
    $arData['id_rota'] = $key;
    $arData['nome'] = $arRotas['NOME'];
    $arData['km'] = $arRotas['KM'];
    $arData['hora_ida_inicio'] = $arRotas['HORA_IDA_INICIO'];
    $arData['hora_ida_termino'] = $arRotas['HORA_IDA_TERMINO'];
    $arData['da_porteira'] = $arRotas['DA_PORTEIRA'] ? 'S' : 'N';
    $arData['da_mataburro'] = $arRotas['DA_MATABURRO'] ? 'S' : 'N';
    $arData['da_colchete'] = $arRotas['DA_COLCHETE'] ? 'S' : 'N';
    $arData['da_atoleiro'] = $arRotas['DA_ATOLEIRO'] ? 'S' : 'N';
    $arData['da_ponterustica'] = $arRotas['DA_PONTERUSTICA'] ? 'S' : 'N';
    $arData['turno_matutino'] = $arRotas['TURNO_MATUTINO'] ? 'S' : 'N';
    $arData['turno_vespertino'] = $arRotas['TURNO_VESPERTINO'] ? 'S' : 'N';
    $arData['turno_noturno'] = $arRotas['TURNO_NOTURNO'] ? 'S' : 'N';
    $arData['shape'] = isset($arRotas['SHAPE']) ? $arRotas['SHAPE'] : null;
    $arData['hora_volta_inicio'] = $arRotas['HORA_VOLTA_INICIO'];
    $arData['hora_volta_termino'] = $arRotas['HORA_VOLTA_TERMINO'];
    $arData['tempo'] = $arRotas['TEMPO'];
    $arData['tipo'] = $arRotas['TIPO'];
    $arData['id_firebase'] = $arRotas['id_firebase'];
    return $arData;
}

function deParaMotoristas($key, $arMotorista) {
    $arData['nome'] = $arMotorista['ANT_CRIMINAIS'];
    $arData['data_nascimento'] = formatDateSQL($arMotorista['DATA_NASCIMENTO']);
    $arData['sexo'] = $arMotorista['SEXO'] == '1' ? 'M' : $arMotorista['SEXO'] == '2' ? 'F' : 'N';
    $arData['cpf'] = $arMotorista['CPF'];
    $arData['telefone'] = $arMotorista['TELEFONE'];
    $arData['cnh'] = $arMotorista['CNH'];
    $arData['ant_criminais'] = empty($arMotorista['ANT_CRIMINAIS']) ? null : $arMotorista['ANT_CRIMINAIS'];
    //$arData['arquivo_docpessoais_anexo'] = $arMotorista[''];
    $arData['tem_cnh_a'] = $arMotorista['TEM_CNH_A'] ? 'S' : 'N';
    $arData['tem_cnh_b'] = $arMotorista['TEM_CNH_B'] ? 'S' : 'N';
    $arData['tem_cnh_c'] = $arMotorista['TEM_CNH_C'] ? 'S' : 'N';
    $arData['tem_cnh_d'] = $arMotorista['TEM_CNH_D'] ? 'S' : 'N';
    $arData['tem_cnh_e'] = $arMotorista['TEM_CNH_E'] ? 'S' : 'N';
    $arData['turno_manha'] = $arMotorista['TURNO_MANHA'] ? 'S' : 'N';
    $arData['turno_tarde'] = $arMotorista['TURNO_TARDE'] ? 'S' : 'N';
    $arData['turno_noite'] = $arMotorista['TURNO_NOITE'] ? 'S' : 'N';
    $arData['id_firebase'] = $arMotorista['id_firebase'];
    return $arData;
}

function deParaGaragem($key, $arGaragem) {
    $arData['loc_cep'] = $arGaragem['LOC_CEP'];
    $arData['loc_endereco'] = $arGaragem['LOC_ENDERECO'];
    $arData['loc_latitude'] = $arGaragem['LOC_LATITUDE'];
    $arData['loc_longitude'] = $arGaragem['LOC_LONGITUDE'];
    $arData['id_firebase'] = $arGaragem['id_firebase'];
    $arData['id_garagem'] = $key;
    return $arData;
}

function deParaAlunos($key, $arAluno) {
    $arData['id_aluno'] = is_numeric($arAluno['id_firebase']) ? $arAluno['id_firebase'] : $key;
    $arData['loc_latitude'] = isset($arAluno['LOC_LATITUDE']) ? $arAluno['LOC_LATITUDE'] : null;
    $arData['loc_longitude'] = isset($arAluno['LOC_LONGITUDE']) ? $arAluno['LOC_LONGITUDE'] : null;
    $arData['loc_endereco'] = isset($arAluno['LOC_ENDERECO']) ? $arAluno['LOC_ENDERECO'] : null;
    $arData['loc_cep'] = isset($arAluno['LOC_CEP']) ? $arAluno['LOC_CEP'] : null;
    $arData['da_porteira'] = isset($arAluno['DA_PORTEIRA']) ? $arAluno['DA_PORTEIRA'] ? 'S' : 'N' : null;
    $arData['da_mataburro'] = isset($arAluno['DA_MATABURRO']) ? $arAluno['DA_MATABURRO'] ? 'S' : 'N' : null;
    $arData['da_colchete'] = isset($arAluno['DA_COLCHETE']) ? $arAluno['DA_COLCHETE'] ? 'S' : 'N' : null;
    $arData['da_atoleiro'] = isset($arAluno['DA_ATOLEIRO']) ? $arAluno['DA_ATOLEIRO'] ? 'S' : 'N' : null;
    $arData['da_ponterustica'] = isset($arAluno['DA_PONTERUSTICA']) ? $arAluno['DA_PONTERUSTICA'] ? 'S' : 'N' : null;
    $arData['nome'] = $arAluno['NOME'];
    $arData['data_nascimento'] = formatDateSQL($arAluno['DATA_NASCIMENTO']);
    $arData['sexo'] = $arAluno['SEXO'];
    $arData['cor'] = isset($arAluno['COR']) && $arAluno['COR'] != 'NAN' ? $arAluno['COR'] : 0;
    $arData['nome_responsavel'] = $arAluno['NOME_RESPONSAVEL'];
    $arData['grau_responsavel'] = isset($arAluno['GRAU_RESPONSAVEL']) ? $arAluno['GRAU_RESPONSAVEL'] : null;
    $arData['telefone_responsavel'] = isset($arAluno['TELEFONE_RESPONSAVEL']) ? $arAluno['TELEFONE_RESPONSAVEL'] : null;
    $arData['def_caminhar'] = isset($arAluno['DEF_CAMINHAR']) && $arAluno['DEF_CAMINHAR'] ? 'S' : 'N';
    $arData['def_ouvir'] = isset($arAluno['DEF_OUVIR']) && $arAluno['DEF_OUVIR'] ? 'S' : 'N';
    $arData['def_enxergar'] = isset($arAluno['DEF_ENXERGAR']) && $arAluno['DEF_ENXERGAR'] ? 'S' : 'N';
    $arData['def_mental'] = isset($arAluno['DEF_MENTAL']) && $arAluno['DEF_MENTAL'] ? 'S' : 'N';
    $arData['turno'] = $arAluno['TURNO'];
    $arData['nivel'] = $arAluno['NIVEL'];
    $arData['cpf'] = isset($arAluno['CPF']) ? $arAluno['CPF'] : null;
    $arData['mec_tp_localizacao'] = isset($arAluno['MEC_TP_LOCALIZACAO']) ? $arAluno['MEC_TP_LOCALIZACAO'] : null;
    $arData['codigo_aluno_firebase'] = $arAluno['id_firebase'];
    return $arData;
}

function deParaEscolas($key, $arEscola) {
    $arData['id_escola'] = !empty($arEscola['ID_ESCOLA']) ? $arEscola['ID_ESCOLA'] : $key;
    $arData['nome'] = $arEscola['NOME'];
    $arData['mec_co_entidade'] = isset($arEscola['MEC_CO_ENTIDADE']) ? $arEscola['MEC_CO_ENTIDADE'] : null;
    $arData['mec_co_uf'] = isset($arEscola['MEC_CO_UF']) ? $arEscola['MEC_CO_UF'] : null;
    $arData['mec_co_municipio'] = $arEscola['MEC_CO_MUNICIPIO'];
    $arData['mec_no_entidade'] = $arEscola['MEC_NO_ENTIDADE'];
    $arData['mec_tp_dependencia'] = $arEscola['MEC_TP_DEPENDENCIA'];
    $arData['mec_tp_localizacao'] = $arEscola['MEC_TP_LOCALIZACAO'];
    $arData['mec_in_regular'] = isset($arEscola['MEC_IN_REGULAR']) && $arEscola['MEC_IN_REGULAR'] ? 'S' : 'N';
    $arData['mec_in_eja'] = isset($arEscola['MEC_IN_EJA']) && $arEscola['MEC_IN_EJA'] ? 'S' : 'N';
    $arData['mec_in_profissionalizante'] = isset($arEscola['MEC_IN_PROFISSIONALIZANTE']) && $arEscola['MEC_IN_PROFISSIONALIZANTE'] ? 'S' : 'N';
    $arData['mec_in_especial_exclusiva'] = isset($arEscola['MEC_IN_ESPECIAL_EXCLUSIVA']) && $arEscola['MEC_IN_ESPECIAL_EXCLUSIVA'] ? 'S' : 'N';
    $arData['loc_latitude'] = isset($arEscola['LOC_LATITUDE']) ? $arEscola['LOC_LATITUDE'] : null;
    $arData['loc_longitude'] = isset($arEscola['LOC_LONGITUDE']) ? $arEscola['LOC_LONGITUDE'] : null;
    $arData['loc_cep'] = isset($arEscola['LOC_CEP']) ? $arEscola['LOC_CEP'] : null;
    $arData['loc_endereco'] = isset($arEscola['LOC_ENDERECO']) ? $arEscola['LOC_ENDERECO'] : null;
    $arData['contato_responsavel'] = isset($arEscola['CONTATO_RESPONSAVEL']) ? $arEscola['CONTATO_RESPONSAVEL'] : null;
    $arData['contato_telefone'] = isset($arEscola['CONTATO_TELEFONE']) ? $arEscola['CONTATO_TELEFONE'] : null;
    $arData['contato_email'] = isset($arEscola['CONTATO_EMAIL']) ? $arEscola['CONTATO_EMAIL'] : null;
    $arData['horario_matutino'] = isset($arEscola['HORARIO_MATUTINO']) && $arEscola['HORARIO_MATUTINO'] ? 'S' : 'N';
    $arData['horario_vespertino'] = isset($arEscola['HORARIO_VESPERTINO']) && $arEscola['HORARIO_VESPERTINO'] ? 'S' : 'N';
    $arData['horario_noturno'] = isset($arEscola['HORARIO_NOTURNO']) && $arEscola['HORARIO_NOTURNO'] ? 'S' : 'N';
    $arData['ensino_superior'] = isset($arEscola['ENSINO_SUPERIOR']) && $arEscola['ENSINO_SUPERIOR'] ? 'S' : 'N';
    $arData['ensino_medio'] = isset($arEscola['ENSINO_MEDIO']) && $arEscola['ENSINO_MEDIO'] ? 'S' : 'N';
    $arData['ensino_fundamental'] = isset($arEscola['ENSINO_FUNDAMENTAL']) && $arEscola['ENSINO_FUNDAMENTAL'] ? 'S' : 'N';
    $arData['ensino_pre_escola'] = isset($arEscola['ENSINO_PRE_ESCOLA']) && $arEscola['ENSINO_PRE_ESCOLA'] ? 'S' : 'N';
    $arData['mec_tp_localizacao_diferenciada'] = isset($arEscola['MEC_TP_LOCALIZACAO_DIFERENCIADA']) ? $arEscola['MEC_TP_LOCALIZACAO_DIFERENCIADA'] : null;
    $arData['codigo_escola_firebase'] = $arEscola['id_firebase'];
    return $arData;
}

function deParaUsers($arUsuario) {
    $arData['codigo_cidade'] = $arUsuario['COD_CIDADE'];
    $arData['uid'] = $arUsuario['ID'];
    $arData['nome'] = $arUsuario['NOME'];
    $arData['cpf'] = $arUsuario['CPF'];
    $arData['telefone'] = $arUsuario['TELEFONE'];
    $arData['email'] = $arUsuario['EMAIL'];
    $arData['password'] = md5($arUsuario['PASSWORD']);
    $arData['cidade'] = $arUsuario['CIDADE'];
    $arData['cod_cidade'] = $arUsuario['COD_CIDADE'];
    $arData['estado'] = $arUsuario['ESTADO'];
    $arData['cod_estado'] = $arUsuario['COD_ESTADO'];
    return $arData;
}

function listarMunicipios() {
    $dbFirbaseMunicipios = new \Db\Sete\FirebaseMunicipios();
    return $dbFirbaseMunicipios->getLista();
}

function populaListaMunicipios() {
    $modelFirebase = new \Application\Model\FirebaseModel();
    $modelFirebase->processarDocumentosMunicipios();
}

function formatDateSQL($dataBR) {
    $data = implode("-", array_reverse(explode("/", $dataBR)));
    return $data;
}

function formataDecimal($number) {
    $value = str_replace(".", "", $number);
    return str_replace(",", ".", $value);
}
