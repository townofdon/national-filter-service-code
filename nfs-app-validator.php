<?php
/*
   // all required
   $val_req = array(
    "firstname", "lastname", "street", "city", "state", "zip",
    "posdesc", "posname", "salary",
    "licensetype", "licenseno", "licensestate",
    "auth_date", "auth_sig",
    "elec_name", "elec_birth", "elec_license", "elec_sig", "elec_date",
    "grade", "canemploy"
   );

   // at least one member of each array must be present
   $val_req_oneof = array(
    array("homeph", "cellph")
   );
   
   // require everything in the target array if given field is present
   $val_req_if = array(
    "convicted" => array("convdate", "convplace", "convdesc"),
    "neednotice" => array("notice"),
    "colgrad" => array("college")
    "hsgrad" => array("highschool");
    "prevapplied" => array("whenapplied");
    "employed" => array("neednotice");
   );

   // require all members if any one is present
   $val_all_if_one = array(
    array("ref1_name", "ref1_title", "ref1_phone"),
    array("ref2_name", "ref2_title", "ref2_phone"),
    array("ref3_name", "ref3_title", "ref3_phone")

  );

   // same thing as above but for jobs
   $val_job_all_if_one = array(
    "to",
    "from",
    "company",
    "title",
    "duties",
    "reason",
    "salary"
   );
   
   function find_in(&$array, &$val) {
    return in_array($val, $array);
   }
   
   function find_if(&$array, &$what, &$vals) {
    if(find_in(&$array, &$what)) {
      $found = 0;
      foreach($vals as &$k) {
          if(!find_in(&$array, &$k)) {
            return 1;
          }
      }
    }
    return 0;
   }
   
   function find_one(&$array, &$vals) {
    foreach($vals as &$v)
      if(find_in($array, &$v))
          return 1;
    return 0;
   }
   
   function find_all_if_one(&$array, &$vals) {
    $miss = 0;
    $hit = 0;
    foreach(&$vals as &$v) {
      if(find_in(&$array, &$v))
          $hit = 1;
      else
          $miss = 1;
      if($hit and $miss) return 0;
    }
    return 1;
   }
   
   
   /* Validate the specified job application, which should already be
    in a parsed form (see app_parse()).
   
    Returns 1 on success, 0 on failure.
   *
   function val_app($app) {
    foreach($val_req as &$v)
      if(!find_in(&$app["detail"], &$v))
          return 0;

    foreach($val_req_one as &$arr)
      if(!find_one(&$app["detail"], &$arr)
          return 0;
   
    foreach($val_req_if as &$v)
      if(!find_if(&$app["detail"], &$v, &$val_req_if[$v]))
          return 0;
         
    foreach($val_req_all_if_one as &$v)
      if(!find_all_if_one(&$app["detail"], &$val_req_all_if_one[$v]))
          return 0;

    foreach($val_job_req_all_if_one as &$v)
      if(!find_all_if_one(&$app["detail"], &$val_job_req_all_if_one[$v]))
          return 0;
         
    return 1;
   }
   
?>
*/
