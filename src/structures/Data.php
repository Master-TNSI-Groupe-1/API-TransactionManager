<?php


namespace structures;

/**
 * Class Data
 * Classe permettant de structurer les retours de l'API.
 * @package structures
 * @OA\Schema(
 *     title="Data model",
 *     description="Modèle de données de l'API."
 * )
 */
class Data
{
    /**
     * @OA\Property(
     *     format="string",
     *     title="Status",
     *     default="error",
     *     description="Status de l'action."
     * )
     *
     * @var string Status de l'action "success" ou "error".
     */
    public $status;

    /**
     * @OA\Property(
     *     format="string",
     *     title="Message",
     *     default="",
     *     description="Message associé au résultat de l'action."
     * )
     *
     * @var string Message associé au résultat de l'action.
     */
    public $message;

    /**
     * @OA\Property(
     *     format="int64",
     *     title="Code",
     *     default=404,
     *     description="Code de retour HTTP."
     * )
     *
     * @var int Code de retour HTTP (200, 201, 404, ...).
     */
    public $code;

    /**
     * @OA\Property(
     *     format="string",
     *     title="Données",
     *     default="",
     *     description="Données retournées par l'API."
     * )
     *
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