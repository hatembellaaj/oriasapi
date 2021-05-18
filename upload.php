<?php
//UPLOAD TREATMENT
if ( isset($_POST["submit"]) ) {
  if ( isset($_FILES["file"])) {
    //if there was an error uploading the file
    if ($_FILES["file"]["error"] > 0) {
      echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
    }
    else {
      echo "Upload: " . $_FILES["file"]["name"] . "<br />";
      echo "Type: " . $_FILES["file"]["type"] . "<br />";
      echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
      echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
      //if file already exists
      if (file_exists("./" . $_FILES["file"]["name"])) {
        echo $_FILES["file"]["name"] . " already exists. ";
      }
      else {
        //Store file in directory "upload" with the name of "uploaded_file.txt"
        $storagename = "uploaded_file.csv";
        move_uploaded_file($_FILES["file"]["tmp_name"], "./" . $storagename);
        echo "Stored in: " . "./" . $_FILES["file"]["name"] . "<br />";
      }
    }
  } else {
    echo "No file selected <br />";
  }
}

 //FILLING DATA TO SOAP CALL 
$intermediaries=[];
if ( isset($storagename) && $file = fopen( "./" . $storagename , "r" ) ) {
  echo "File opened.<br />";
  $fields = array();
  $line = array();
  $i = 0;
  //CSV: one line is one record and the cells/fields are seperated by ";"
  while ( $line[$i] = fgets ($file, 4096) ) {
    $intermediaries[$i] = array('siren' => trim($line[$i])); 
    $i++;
  }
}


//Preparing file 
header('Content-type: text/csv');
header('Content-Disposition: attachment; filename="demo.csv"');
    
// do not cache the file
header('Pragma: no-cache');
header('Expires: 0');



//SLICING DATA
$k=0;
while ($k< count($intermediaries)) 
{

  // create a file pointer connected to the output stream
  $file = fopen('php://output', 'a');
      
  // send the column headers
  fputcsv($file, array('sirene', 'denomination', 'categoryName', 'AGA'));

  $wsdl   = 'https://ws.orias.fr/service?wsdl';
  $client = new SoapClient($wsdl, array('trace'=>1));  // The trace param will show you errors
  $user= 'WUU7EOCWD0GX9GHZX6Q5';
  $end =(count($intermediaries)-$k > 1000)?$k+1000:count($intermediaries)-$k;

  $request_param = array(
      'user' => $user,
      'intermediaries' => array_slice($intermediaries, $k, $end)
  );

  //SOAP API CALL
  try {
    $responce_param = $client->intermediarySearch($request_param);
    $res = $responce_param->intermediaries->intermediary ;
    $data = array();
    $j=0;

    foreach ($res as $r)
    {
      if (gettype($r->registrations->registration)=="array")
      {
        $categories = "";
        foreach ($r->registrations->registration as $reg)
        $categories = $categories . " " . $reg->categoryName ;
        $data [$j] = [$r->informationBase->siren,
          $r->informationBase->denomination, 
          $categories,
          strpos($categories,'AGA')?1:0
          ];
      }
      else
      {
        $data [$j] = [$r->informationBase->siren,
        $r->informationBase->denomination, 
        $r->registrations->registration->categoryName,
        strpos($categories,'AGA')?1:0 ];
      }
      $j++;
    }
  } catch (Exception $e) {
    echo "<h2>Exception Error</h2>";
    echo $e->getMessage();
  }

  // output each row of the data
  foreach ($data as $row)
  {
    fputcsv($file, $row);
  }
  $k = $k+1000;
  fclose($file);
  sleep(2);
} 
exit();
?>