<?php
function amazonInfo($isbn, $public_key, $private_key){
 
    $result = array('error'=>'');
   $regions = array('com','de','co.uk','fr');
   
    foreach($regions as $region){
        $pxml = aws_signed_request($region, array("Operation"=>"ItemLookup","ItemId"=>$isbn,"ResponseGroup"=>"Large"), $public_key, $private_key);

        if ($pxml === False){
           
            $result['error']="Did not work.";
       
        }elseif(isset($pxml->Items->Item->ItemAttributes->Title)){
           //print_r($pxml);
            $result['Title']=$pxml->Items->Item->ItemAttributes->Title;
         $authors = array();
         foreach($pxml->Items->Item->ItemAttributes->Author as $author) array_push($authors, "$author");
         foreach($pxml->Items->Item->ItemAttributes->Creator as $author) array_push($authors, "$author");
         $result['Author']=implode(', ',$authors);
            $result['Publisher']=$pxml->Items->Item->ItemAttributes->Publisher;
            $result['Language']=$pxml->Items->Item->ItemAttributes->Languages->Language->Name;
            $result['ISBN']=$pxml->Items->Item->ItemAttributes->ISBN;
            $result['EAN']=$pxml->Items->Item->ItemAttributes->EAN;
            $result['ASIN']=$pxml->Items->Item->ItemAttributes->ASIN;
            $result['Edition']=$pxml->Items->Item->ItemAttributes->Edition;
            $result['Pages']=$pxml->Items->Item->ItemAttributes->NumberOfPages;
            $result['Year']=substr($pxml->Items->Item->ItemAttributes->PublicationDate,0,4);
            $result['Image']=$pxml->Items->Item->LargeImage->URL;
            if(isset($pxml->Items->Item->EditorialReviews->EditorialReview->Content))
            $result['Content']=$pxml->Items->Item->EditorialReviews->EditorialReview->Content;
            else $result['Content']='';
            break;
           
        }else{
   
         $result['error']="Could not find item.";
      }
   
    }
     
    return $result;
   
}

function aws_signed_request($region, $params, $public_key, $private_key)
{
    /*
    Parameters:
        $region - the Amazon(r) region (ca,com,co.uk,de,fr,jp)
        $params - an array of parameters, eg. array("Operation"=>"ItemLookup",
                        "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
        $public_key - your "Access Key ID"
        $private_key - your "Secret Access Key"
    */

    // some paramters
    $method = "GET";
    $host = "ecs.amazonaws.".$region;
    $uri = "/onca/xml";
   
    // additional parameters
    $params["Service"] = "AWSECommerceService";
    $params["AWSAccessKeyId"] = $public_key;
    $params["AssociateTag"] = "lib";
    // GMT timestamp
    $params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
    // API version
    $params["Version"] = "2009-03-31";
   
    // sort the parameters
    ksort($params);
   
    // create the canonicalized query
    $canonicalized_query = array();
    foreach ($params as $param=>$value)
    {
        $param = str_replace("%7E", "~", rawurlencode($param));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $canonicalized_query[] = $param."=".$value;
    }
    $canonicalized_query = implode("&", $canonicalized_query);
   
    // create the string to sign
    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
   
    // calculate HMAC with SHA256 and base64-encoding
    $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $private_key, True));
   
    // encode the signature for the request
    $signature = str_replace("%7E", "~", rawurlencode($signature));
   
    // create request
    $request = "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
   
//echo $request;
    // do request
    $response = @file_get_contents($request);
   
    if ($response === False)
    {
        return False;
    }
    else
    {
      //print_r($response);
        // parse XML
        $pxml = simplexml_load_string($response);
        if ($pxml === False)
        {
            return False; // no xml
        }
        else
        {
            return $pxml;
        }
    }
}

unset($response);
?>