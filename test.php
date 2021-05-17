<?php

$wsdl   = 'https://ws.orias.fr/service?wsdl';
$client = new SoapClient($wsdl, array('trace'=>1));  // The trace param will show you errors
$user= 'WUU7EOCWD0GX9GHZX6Q5';
$intermediaries=[];
$intermediaries[0]= array('siren' => '332116466');
$intermediaries[1]= array('siren' => '308316819');
$intermediaries[2]= array('siren' => '393497987');
$intermediaries[3]= array('siren' => '449068410');
// web service input param
$request_param = array(
    'user' => $user,
    'intermediaries' => $intermediaries
);
try {
    $responce_param = $client->intermediarySearch($request_param);
    //echo $siren . ' siren => ' . $responce_param->intermediaries . ' informationBase';
   // var_dump($responce_param->intermediaries->intermediary[0]->informationBase->siren) ;
// output headers so that the file is downloaded rather than displayed

//var_dump($responce_param);
//die();

header('Content-type: text/csv');
header('Content-Disposition: attachment; filename="demo.csv"');
 
// do not cache the file
header('Pragma: no-cache');
header('Expires: 0');

// create a file pointer connected to the output stream
$file = fopen('php://output', 'w');
 
// send the column headers
fputcsv($file, array('sirene', 'denomination', 'categoryName'));





$res = $responce_param->intermediaries->intermediary ;
$data = array();
// Sample data. This can be fetched from mysql too
$i=0;
foreach ($res as $r)
{
    //var_dump($r->registrations->registration);
    //die();

 /*  
*/

if (gettype($r->registrations->registration)=="array")
{

    $categories = "";
    $j=0;
    foreach ($r->registrations->registration as $reg)
    {
        $categories = $reg[$j]->categoryName . " ";
        $j++;
    }




$data [$i] = [$r->informationBase->siren,
$r->informationBase->denomination, 
$categories ];
}
else
$data [$i] = [$r->informationBase->siren,
$r->informationBase->denomination, 
$r->registrations->registration->categoryName ];
//, $r->registrations->registration     [0]->categoryName.strcmp("AGA") ? 1 : 0 
$i++;
}
// output each row of the data
foreach ($data as $row)
{
fputcsv($file, $row);
}
 
exit();



    } catch (Exception $e) {
    echo "<h2>Exception Error</h2>";
    echo $e->getMessage();
}

?>