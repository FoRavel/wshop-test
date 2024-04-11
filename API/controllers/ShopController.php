<?php

namespace FwTest\Controller;

use FwTest\Classes as Classes;

class ShopController extends AbstractController
{
    /**
     * @Route('/shop_list.php')
     */
    public function index()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $filters = [];

        if (isset($_GET['magasin_nom'])) {
            $filters = [...$filters, "magasin_nom" => $_GET['magasin_nom']];
        }

        if (isset($_GET['magasin_type'])) {
            $filters = [...$filters, "magasin_type" => $_GET['magasin_type']];
        }

        $db = $this->getDatabaseConnection();

        $list_shop = Classes\Shop::getAll($db, 0, $this->array_constant['shop']['nb_shops'], $filters);

        echo json_encode(['results' => $list_shop]);
    }

    /**
     * @Route('/shop_create.php')
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $db = $this->getDatabaseConnection();
        $encodedDatas = file_get_contents('php://input');
        $decodedDatas = json_decode($encodedDatas, true);
        if ($decodedDatas === null || !isset($decodedDatas['magasin_nom'], $decodedDatas['magasin_type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données JSON invalides ou incomplètes']);
            return;
        }
        http_response_code(201);
        echo json_encode(Classes\Shop::post($db, $decodedDatas));
    }

    /**
     * @Route('/shop_edit.php')
     */
    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $db = $this->getDatabaseConnection();
        $encodedDatas =  file_get_contents('php://input');
        $decodedDatas = json_decode($encodedDatas, true);
        if ($decodedDatas === null || !isset($decodedDatas['magasin_nom'], $decodedDatas['magasin_type'], $_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données JSON invalides ou incomplètes']);
            return;
        }
        http_response_code(201);
        $shop = new Classes\Shop($db, $_GET['id']);
        echo json_encode($shop->update($decodedDatas));
    }

    /**
     * @Route('/shop_delete.php')
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $db = $this->getDatabaseConnection();
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Id required']);
            return;
        }
        http_response_code(204);
        $shop = new Classes\Shop($db, $_GET['id']);
        echo json_encode($shop->delete());
    }
}
