<?php 

class Stat {

    public function __construct()
    {
        header("Content-type: application/json");
        require_once __DIR__ . '/../../app/functions.php';
    }
    function dashboard(){
        $url = $_GET['url'] ?? null;
        $date = $_GET['date'] ?? 'all';
        $limit = $_GET['limit'] ?? -1;
        $status = $_GET['status'] ?? 'all';
        
       
        $config = serviceAuditorConfig();
        $results = serviceAuditorLoadResults($config, $date, $status, $limit, $url);
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