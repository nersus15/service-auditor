<?php 

class Stat {

    public function __construct()
    {
        header("Content-type: application/json");
        require_once __DIR__ . '/../../app/functions.php';
    }
    function dashboard(){

        // declare(strict_types=1);
        $config = serviceAuditorConfig();
        $url = $_GET['url'] ?? null;
        $results = serviceAuditorLoadResults($config, $url);
        $summary = serviceAuditorSummarize($results);
        $recentResults = $results;


        $data = [
            "summary" => $summary,
            "results" => $recentResults,
            "url" => $url
        ];

        echo json_encode($data);
        http_response_code(200);
    }

    function test(){
        echo "OK2";
    }
}