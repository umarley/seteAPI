<?php
namespace Sete\V1\Rest\Municipios;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class MunicipiosResource extends \Sete\V1\API
{
    
    public function __construct() {
        parent::__construct();
        $this->_model = new MunicipiosModel();
    }
    
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $this->usuarioPodeGravar();
        return new ApiProblem(405, 'The POST method has not been defined');
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        $this->usuarioPodeGravar();
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        return $this->_model->getById($id);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'mapa';
        switch ($tipo){
            case 'mapa':
                $this->populaResposta(200, $this->_model->getAll());
                break;
            case 'lista':
                $pagina = (isset($_GET['pagina']) ? $_GET['pagina'] : 1);
                $busca = (isset($_GET['busca']) ? $_GET['busca'] : "");
                $this->populaResposta(200, $this->_model->getListaPaginada($pagina, $busca), false);
                break;
            case 'excel':
                $this->populaResposta(200, $this->_model->processarExcel(), false);
                break;
        }
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        $this->usuarioPodeGravar();
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
