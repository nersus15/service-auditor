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
        $results = serviceAuditorLoadResults($config);
        $summary = serviceAuditorSummarize($results);
        $recentResults = $results;


        $data = [
            "summary" => $summary,
            "results" => $recentResults
        ];

        echo json_encode($data);
        http_response_code(200);
    }

    function test(){
        echo "OK2";
    }
}