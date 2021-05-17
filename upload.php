<?php


if ( isset($_POST["submit"]) ) {

    if ( isset($_FILES["file"])) {
 
             //if there was an error uploading the file
         if ($_FILES["file"]["error"] > 0) {
             echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
 
         }
         else {
                  //Print file details
              echo "Upload: " . $_FILES["file"]["name"] . "<br />";
              echo "Type: " . $_FILES["file"]["type"] . "<br />";
              echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
              echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
 
                  //if file already exists
              if (file_exists("./upload/" . $_FILES["file"]["name"])) {
             echo $_FILES["file"]["name"] . " already exists. ";
              }
              else {
                     //Store file in directory "upload" with the name of "uploaded_file.txt"
             $storagename = "uploaded_file.csv";
             move_uploaded_file($_FILES["file"]["tmp_name"], "./upload/" . $storagename);
             echo "Stored in: " . "upload/" . $_FILES["file"]["name"] . "<br />";
             }
         }
      } else {
              echo "No file selected <br />";
      }
 }

 $intermediaries=[];

 if ( isset($storagename) && $file = fopen( "upload/" . $storagename , "r" ) ) {

  echo "File opened.<br />";
  $fields = array();
  $line = array();
  $i = 0;

      //CSV: one line is one record and the cells/fields are seperated by ";"
      //so $dsatz is an two dimensional array saving the records like this: $dsatz[number of record][number of cell]
  while ( $line[$i] = fgets ($file, 4096) ) {

      
    $intermediaries[$i] = $line[$i];

      $i++;
  }


}



var_dump($intermediaries);
die();

$wsdl   = 'https://ws.orias.fr/service?wsdl';
$client = new SoapClient($wsdl, array('trace'=>1));  // The trace param will show you errors
$user= 'WUU7EOCWD0GX9GHZX6Q5';
/*$intermediaries=[];
$intermediaries[0]= array('siren' => '332116466');
$intermediaries[1]= array('siren' => '308316819');
$intermediaries[2]= array('siren' => '393497987');
$intermediaries[3]= array('siren' => '449068410');*/
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



if (gettype($r->registrations->registration)=="array")
{

    $categories = "";
    foreach ($r->registrations->registration as $reg)
        $categories = $categories . " " . $reg->categoryName ;





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