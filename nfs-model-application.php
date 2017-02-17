<?php
/* model-application.php */

/* Functions for manipulating the application data, including both application details and previous
 job history.

 Application data exists in three forms:
 form submission data <=> application object <=> database

 For the application object, I opted to use hierarchical array trees:

 application  = array( "details" => detail_data,
     "jobs" => job_data )

 detail_data  = array( "firstname" => ..., ..., "hsgrad" => ..., ... )

 job_data   = array(
  //first job
  [0] => array("company" => ..., ...),
  //second job
  [1] => array( ... ),
  ...
 )

 The allowed fields are listed in the arrays below. The $appfield_text
 and $appfield_bool arrays have their strings used as indexes in the
 application["details"] array.  The $jobfield array has its indexes used
 in the subarrays of application["jobs"].

 Special handling is used for referral data (i.e., where the applicant
 heard about us).

 Theoretically, there are two properties:
 reftype   - the type of source, e.g., newspaper
 refdetail - a description of the particul source, e.g., The Washington Post

 However, the data is expected to come in as
   reftype
   refdetail_[$reftype]  --> that is, "refdetail_" with the contents of reftype appended.

 An application object instance always has all the detail_data filled in.
 It may have an empty array for the job_data, indicating no job
 information, but for each subarray of job_data actually in the
 job_data array, that subarray has all $jobfield members filled in.
 */

/********** USER SUBMISSION PARSER DATA *************/
// allowed incoming application fields by text and bool types
// list type    html type               db type
// text         text/textarea/radio/etc.   varchar(255)
// bool         checkbox                (tiny)int(1)

// Note: boolean values may be "" (unknown), 0 (false), or 1 (true).

$appfield_text = array(
    "firstname", "lastname", "whenapplied", "street", "city", "state", "zip",
    "homeph", "cellph", "email", "posdesc", "posname", "salary",
    "military", "licensetype", "licenseno", "licensestate", "convdate", "convplace",
    "convdesc", "notice", "ref1_name", "ref1_title", "ref1_phone", "ref2_name",
    "ref2_title", "ref2_phone", "ref3_name", "ref3_title", "ref3_phone", "auth_date", "auth_sig",
    "elec_name", "elec_birth", "elec_ssn", "elec_license", "elec_sig", "elec_date",
    "grade", "highschool", "college", "ssn", "referredfrom",
    /* new fields added by Chiedo Labs - https://labs.chie.do */
    /* all consumer report fields are prefixed with 'cons_' */
    "cons_sig",
    "cons_date",
    /* all disclosure confirmation fields are prefixed with 'disc_' */
    "disc_name",
    "disc_other_names",
    "disc_ssn",
    "disc_birth",
    "disc_license",
    "disc_license_state",
    "disc_city",
    "disc_state",
    "disc_zip",
    "disc_sig",
    "disc_date",
    "disc_employer",
    /* new fields 1.17.2017 */
    "nickname",
    "pref_contact_method"
);
$appfield_bool = array(
    "prevapplied",
    "hsgrad",
    "colgrad",
    "convicted",
    "employed",
    "neednotice",
    "canemploy",
    /* new fields added by Chiedo Labs - http://labs.chie.do */
    /* all consumer report fields are prefixed with 'cons_' */
    "cons_receive_copy",
    /* all disclosure confirmation fields are prefixed with 'disc_' */
    "disc_have_read",
    "disc_binding",
    /* new bool fields 1.17.2017 */
    "can_contact_emp"
);

# special ref* fields:
#
#   app_parse() input         =>  application object (e.g., app_save())
#   ---------------------           -------------------------------------
#   reftype       fiel  d     =>   reftype
#   refdetail_* fields (depends)  =>   refdetail
#
# Allowed values of reftype field
$reflist = array( "online", "radio", "newspaper", "other", "wordOfMouth", "commission", "craigslist" );
 
// allowed incoming job fields
$jobfield = array(
   "to",
   "from",
   "company",
   "title",
   "duties",
   "reason",
   "salary"
);

    // Returns a new empty application object (with 3 empty jobs)
    // NOTE: boolean fields are set to "" (unknown).
    function app_empty() {
      global $appfield_text;
      global $appfield_bool;
      global $jobfield;

      $detail = array();
      foreach($appfield_text as $key)
          $detail[$key] = "";
      foreach($appfield_bool as $key)
          $detail[$key] = "";

      $jobs = array();
      for($i=0; $i<4; $i++) {
          $jobs[$i] = array();
          foreach($jobfield as $key) {
            $jobs[$i][$key] = "";
          }
      }

      $app = array("details" => $detail, "jobs" => $jobs);
      return $app;
    }

    // Parse data from the user (not database) about an application,
    // WITHOUT sanitizing the data.  Returns the parsed data in
    // the application format described earlier (suitable for
    // passing to app_save(), which will sanitize).
    //
    // Unexpected or malformed data from the input are excised
    // instead of throwing errors.  Missing data will be filled
    // in with default values.
    //
    // In particular,
    // - unknown field names are skipped
    // - job fields for jobs numbered other than 0-9 are ignored
    //
    // WARNING: Returned data is not sanitized.
    function app_parse($data) {
      global $appfield_text, $appfield_bool, $jobfield, $reflist;

      /*
        header("Content-type: text/plain");
        print_r($_POST);
        echo "\n\n";
        */


      // build initial default empty subobjects
      $details = array();
      foreach($appfield_text as $key)
          $details[$key] = "";
      foreach($appfield_bool as $key)
          $details[$key] = "";
      $job = array();

      foreach($data as $key => $val) {
          if(in_array($key, $appfield_text)) {
            $val = $val;
          }
          elseif(in_array($key, $appfield_bool)) {
            $val = ($val ? "1" : "0");
          }
          // possible job entry?
          elseif(substr($key, 0, 4) == "prev") {
            // make sure it is plausibly long
            $len = strlen($key);
            if($len < 6)
                continue;

            // grab appropriate parts
            $num = intval($key[4]);
            $field = substr($key, 6, $len);

            // make sure the number is acceptable (0-9)
            if($num < 0 or $num > 9) {
                continue;
            }

            // verify job field
            if(!in_array($field, $jobfield))
                continue;

            // make sure this job array exists
            if(!isset($job[$num])) {
                // fill in complete but blank subarray
                $job[$num] = array();
                foreach($jobfield as $k) {
                  $jobs[$num][$k] = "";
                }
            }

            $job[$num][$field] = $val;
                continue;
          }
          // unknown field
          else {
            // skip unknown fields
            continue;
          }

          // fall-through for non-job entries
          $details[$key] = $val;
      }

      // remove any completely empty/default jobs -- pardon my variables!
      for($j=0; $j<=9; $j++) {
          if(isset($job[$j])) {
            $jb = $job[$j];
            $empty = true;
            for($f=0; $f<count($jobfield); $f++) {
                if(isset($jb[$jobfield[$f]]) and $jb[$jobfield[$f]]!=="") {
                  $empty = false;
                  break;
                }
            }
            if($empty) {
                unset($job[$j]);
            }
          }
      }
      $num = 0;
      $jb = array();
      for($j=0;$j<=9; $j++) {
          if(isset($job[$j])) {
            $jb[$num] = $job[$j];
            $num++;
          }
      }

      // special referral processing
      if(isset($data["reftype"])) {
          $j = $data["reftype"];
          if($j == "craigslist") {
            $data["refdetail_online"] = "craigslist";
            $j = "online";
          }
          if(!in_array($j, $reflist))
            $j = "other";
          $details["reftype"] = $j;
           
          if(isset($data["refdetail_" . $j]))
            $j = $data["refdetail_" . $j];
          else
            $j = "";
          $details["refdetail"] = $j;
      }
      else {
          $details["reftype"] = "other";
          $details["refdetail"] = "";
      }

      // Complete and return the application object
      return array(
         "jobs" => $jb,
         "details" => $details
      );
    }


    // delete the user with the specified aid
    // WARNING: only removes from the overview table
    //  ... any previous jobs, tokens, attachments, etc., are retained.
    //      for that, delete from the other tables.
    // Note: This is used if for some reason we can create a user account
    //    but not save an application.  The code that invokes it also
    //    logs this rather serious error condition.
    //    ... probably should be merged somehow into app_save().
    function _del_aid($aid) {
      global $db;
      $sane_aid = $db->escape_string($aid);
      $res = $db->query("DELETE FROM overview WHERE aid=$sane_aid");
      return $res;
    }

    // Save an application (that should have already been parsed)
    // to the specified application id (= account).
    //
    // TODO: make it smart enough to generate an AID as needed.
    //    I can't help but think of the unlikely (erroneous)
    //    case that an AID can be reserved in overview but
    //    app_save() fails out on other tables--this means a
    //    waste of temporary aids.  Using table locking rather than
    //    a transactional approach could prevent this I guess.
    //
    // The save is done in a single database transaction
    // and rolled back in the case of errors.
    //
    // Return 1 on success, 0 on (database) error.
    // On error, $save_error is set with an error string.
    function app_save($parsed, $aid) {
      global $save_error, $err;
      global $db;
       
      // start a transaction
      $res = $db->query("START TRANSACTION");
      if(!$res) {
          $save_error = "START: " . $db->error;
          return 0;
      }

      // save details
      $res = app_save_detail($parsed["details"], $aid);
      if(!$res) {
          $save_error = "DETAIL: " . $db->error;
          $db->query("ROLLBACK");
          return 0;
      }

      // save jobs
      $res = app_save_jobs($parsed["jobs"], $aid);
      if(!$res) {
          $save_error = "JOBS: " . $db->error;
          $db->query("ROLLBACK");
          return 0;
      }
       
      // save longterm data
      $res = app_save_track($parsed, $aid);
      if(!$res) {
          $save_error = "TRACK: " . $err;
          $db->query("ROLLBACK");
          return 0;
      }

      // update application modification date
      $res = app_touch_mod($aid);
      if(!$res) {
          $save_error = "TOUCH: " . $db->error;
          $db->query("ROLLBACK");
          return 0;
      }

      // finish the transaction
      $res = $db->query("COMMIT");
      if(!$res) {
          $save_error = "COMMIT: " . $db->error;
          return 0;
      }

      return 1;
    }
     
   
    function app_save_track($parsed, $aid) {
      $detail = $parsed["details"];
       
      //print_r($parsed);
      $track = new Track(
          $aid,
          $detail["firstname"],
          $detail["lastname"],
          $detail["state"],
          $detail["reftype"],
          $detail["refdetail"],
          $detail["prevapplied"]
      );
       
      return $track->save();
    }

     
    // Save all jobs in the job data for the given application id.
    // The existing jobs are dropped and replaced with this set.
    // Also, if jobs in the array have missing fields, then those
    // fields are blanked in the database.
    //
    // $data[$jobnum] = array(
    //  $job_field => $value,
    //  ...
    //  );
    //
    function app_save_jobs($data, $aid) {
      global $db;

      $sane_aid = $db->escape_string($aid);

      $q = "DELETE FROM prevjobs WHERE aid='$sane_aid'";
      $res = $db->query($q);
      if(!$res) return 0;

      for($jn = 0; $jn<=9; $jn++) {
          if(isset($data[$jn])) {
            $q1 = "`aid`";
            $q2 = "'$sane_aid'";
            $job = $data[$jn];
            $i = 0;
            foreach($job as $key => $val) {
                $skey = $db->escape_string($key);
                $sval = $db->escape_string($val);
                $q1 .= ", `$skey`";
                $q2 .= ", '$sval'";
            }

            $q = "INSERT INTO prevjobs ($q1) VALUES ($q2)";
            //echo "$jn: $q<br>";
            $res = $db->query($q);
            if(!$res) {
                return 0;
            }
          }
      }
      return 1;
    }

    function app_save_detail($data, $aid) {
      global $db;

      // sanitize aid
      $sane_aid = $db->escape_string($aid);

      // delete old application detail
      $q = "DELETE FROM detail WHERE aid='$sane_aid'";
      $res = $db->query($q);
      if($res === FALSE) {
          return 0;
      }

      // prepare new details for insertion
      $q1 = "`aid`";
      $q2 = "'$sane_aid'";
      $i = 0;
      foreach($data as $key => $val) {
          $skey = $db->escape_string($key);
          $sval = $db->escape_string($val);
          $q1 .= ", `$skey`";
          $q2 .= ", '$sval'";
      }

      $q = "INSERT INTO detail ($q1) VALUES ($q2)";

      // execute query
      $res = $db->query($q);
      if($res === FALSE) {
          return 0;
      }

      return 1;
    }


    /*
    * Read in a single application's data and convert it
    * to match the logic behind the display page.
    *
    * You should only call this if you know there is an application
    * (see model/user.php for details on user status)
    *
    * @param $aid - the ID of the application to load
    *
    * @return the application data array
    *
    */
    function app_load($aid) {
      global $db;

      // sanitize data
      $aid = $db->escape_string($aid);

      // Load summary table data
      $res = $db->query("SELECT * FROM detail WHERE aid='$aid'");
      if($res === FALSE) {
          logfail('Database error: ' . $db->error);
      }
      if($res->num_rows != 1) {
          logfail('Database error: wrong number of rows (expected 1 but got ' . $res->num_rows . ')');
      }
      $row = $res->fetch_assoc();

      // copy results and close query
      $info = array("details" => $row);
      $res->close();

      // query from previous jobs table
      $res = $db->query("SELECT * FROM prevjobs WHERE aid=$aid");
      if($res === FALSE) {
          logfail('Database error: ' . $db->error);
      }

      // create job array
      $info["jobs"] = array();

      // copy in data
      $i=0;
      while($row = $res->fetch_assoc()) {
          $info["jobs"][$i] = array();

          // Process data
          foreach($row as $key => $val) {
            switch($key) {
                case "aid":
                  break;
                case "fromdate":
                case "todate":
                  if($key == "fromdate")
                  $key = "from";
                  else
                  $key = "to";
                default:
                  $info["jobs"][$i][$key] = $val;
                  break;
            }
          }

          $i++;
      }

      $res->close(); // close job table query

      return $info;
    }

    // update modification date for an application
    function app_touch_mod($aid) {
      return user_touch_mod($aid);
    }

    // update viewdate for an application
    function app_touch_view($aid) {
      return user_touch_view($aid);
    }

    // HTML escape the fields of details and jobs in an application
    // Changed in place
    function app_htmlescape(&$app) {
      $details = &$app["details"];
      $jobs = &$app["jobs"];

      $keys = array_keys($details);
      foreach($keys as $key) {
          $details[$key] = htmlentities($details[$key]);
      }

      for($i=1; isset($jobs[$i]); $i++) {
          $j = &$jobs[$i];
          $keys = array_keys($j);
          foreach($keys as $key) {
            $j[$key] = htmlentities($j[$key]);
          }
      }
    }

?>


