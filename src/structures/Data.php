<?php


namespace structures;

/**
 * Class Data
 * Classe permettant de structurer les retours de l'API.
 * @package structures
 */
class Data
{
    /**
     * @var string Status de l'action "success" ou "error".
     */
    public $status;

    /**
     * @var string Message associé au résultat de l'action.
     */
    public $message;

    /**
     * @var int Code de retour HTTP (200, 201, 404, ...).
     */
    public $code;

    /**
     * @var string Données retournées par l'API.
     */
    public $data;

    /**
     * Data constructor Par défaut renvoie une erreur.
     */
    public function __construct()
    {
        $this->status = "error";
        $this->code = 404;
        $this->message = "";
        $this->data = "";
    }
}