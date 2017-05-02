<?php
/* admin view */

   $title = "Application View";
   $page_css = "css/adminview.css";
   include("pagewrap.inc.php");


  // selected checkbox / radio button
  $SELECT = 'checked="checked"';
  $SEL2 = 'selected="selected"';

  // old database field notice
  $OLD_FIELD_NOTICE = 'No database entry for this field. This may be from an old application. If so, the fields below do not accurately represent information that was captured at the time of the application.';

  $status = intval($mode);

  // HTML escape values
   app_htmlescape($app);
   $jobs = $app["jobs"];
   $app = $app["details"];

   //Some experiments for PHP-based validation
   //$error = array("page" => array("firstname"));
   //$pgerr = array("att_desc_0" => 1, "prev0_salary" => 1);
   
   /* Map $app["reftype"] and $app["refdetail"] into a hashtable based on the
    actual contents of the webpage.
  */
   $hears = array(
    "newspaper" => false,
    "craigslist" => false,
    "radio" => false,
    "commission" => false,
    "wordOfMouth" => false,
    "online" => false,
    "other" =>false
   );
   $heard = array(
    "newspaper" => "",
    "radio" => "",
    "online" => "",
    "other" => ""
   );
   
   $data = &$app;
   $hs = isset($data["reftype"]) ? $data["reftype"] : "other";
   if(!isset($hears[$hs])) $hs = "other"; // unknown type
   switch($hs) {
    case "online":
    case "radio":
    case "newspaper":
    case "other":
      $heard[$hs] = isset($data["refdetail"]) ? $data["refdetail"] : "";
      if($hs == "online" && $heard[$hs] == "craigslist") {
          $hs = "craigslist";
          $heard["online"] = "";
      }
      // fall-through
    case "craigslist":
    case "commission":
    case "wordOfMouth":
      $hears[$hs] = true;
      break;
    default:
      // should not be able to get here
      logfail(__FILE__ + ":" + __LINE__ + " assertion failed: hearsrc default in switch");
      break;
   }


?>

<table border=1 cellspacing=5 cellpadding=5>
<tr>
<td colspan=12>
NFS is an Equal Opportunity Employer.  Our hiring decisions are made without regard to race, color, religion, political affiliation, national origin, disability, marital status, gender, or age.


<tr >
<td colspan=12>
<span class="heading">Personal Information</span>
<tr>
<td colspan=6 >
First Name: <span class="userdata"><?php echo $app["firstname"] ?></span>
</td>
<td colspan=6 >
Last Name: <span class="userdata"><?php echo $app["lastname"] ?></span>
</td>
</tr>
<tr>
<td colspan=12>
Preferred Name/Nickname: <span class="userdata"><?php echo $app["nickname"] ?></span>
</td>
</tr>
<tr>
<td colspan=3>
Street Address: <span class="userdata"><?php echo $app["street"] ?></span>
<td colspan=3>
City: <span class="userdata"><?php echo $app["city"] ?></span>
<td colspan=3>
State: <span class="userdata"><?php echo $app["state"] ?></span>
<td colspan=3>
Zip Code: <span class="userdata"><?php echo $app["zip"] ?></span>
</tr>
<tr>
<td colspan=6 >
Home Phone No.: <span class="userdata"><?php echo $app["homeph"] ?></span>
<td colspan=6 >
Cell Phone No.: <span class="userdata"><?php echo $app["cellph"] ?></span>
</tr>
<tr>
<td colspan=6>
Email Address: <span class="userdata"><?php echo $app["email"] ?></span>
</td>
<td colspan=6>
Preferred method of contact: <span class="userdata"><?php echo $app["pref_contact_method"] ?></span>
</td>
</tr>


<tr>
<td colspan=12>
<span class="heading">Employment Desired</span>
<tr>
<td colspan=12>
Position Applying for?
<span class="userdata">
<?php if($app["posname"] == "svctech") echo "Service Technician";
    elseif ($app["posname"] == "office") echo "Office Staff";
    elseif ($app["posname"] == "other") echo "Other";
?></span>
<tr>
<td colspan=12>
If other, please describe.<br>
<p><span class="userdata"><?php echo $app["posdesc"]?></span></p>
</tr>
<tr>
<td colspan=12>
How did you hear of this position?<br>
   <table id="hearsrc_source" <?php echo ((isset($pgerr["hearsrc_source"]) or isset($pgerr["hearsrc_detail"]))?"class='error'":"");?> cellspacing=10 cellpadding=3>
   <tr>
   <td style="border:none"><input name="reftype" type="radio" value="craigslist" <?php echo ($hears["craigslist"] ? $SELECT : "") ?> >
   <td><span style="font-weight:bold">Craigslist</span>
   <tr>
   <td style="border:none"><input name="reftype" type="radio" value="newspaper"  <?php echo ($hears["newspaper"] ? $SELECT : "") ?> >
   <td><span style="font-weight:bold">Newspaper</span><br>Which newspaper?  <input id="refdetail_newspaper" name="hearsrc_newspaper" type="text" value="<?php echo $heard["newspaper"] ?>" >
   <tr>
   <td style="border:none"><input name="reftype" type="radio" value="radio" <?php echo ($hears["radio"] ? $SELECT : "") ?> >
   <td><span style="font-weight:bold">Radio</span><br>Which station?  <input id="refdetail_radio" name="hearsrc_radio" type="text" value="<?php echo $heard["radio"] ?>" >
   <tr>
   <td style="border:none"><input name="reftype" type="radio" value="commission" <?php echo ($hears["commission"] ? $SELECT : "") ?> >
   <td><span style="font-weight:bold">Employment Commission</span>
   <tr>
   <td style="border:none"><input name="reftype" type="radio" value="wordOfMouth" <?php echo ($hears["wordOfMouth"] ? $SELECT : "") ?> >
   <td><span style="font-weight:bold">Word of Mouth</span>
   <tr>
   <td style="border:none"><input name="reftype" type="radio" value="online" <?php echo ($hears["online"] ? $SELECT : "") ?> >
   <td><span style="font-weight:bold">Online</span><br>What is the web address?<br><input id="hrefdetail_online" name="hearsrc_online" style="width:80%" type="text" value="<?php echo $heard["online"] ?>">
   <tr>
   <td style="border:none"><input name="reftype" type="radio" value="other" <?php echo ($hears["other"] ? $SELECT : "") ?> >
   <td><span style="font-weight:bold">Other</span><br>How?  <input id="hearsrc_other" name="refdetail_other" type="text" value="<?php echo $heard["other"] ?>">
   </table>

<tr>
<td colspan=6>
Salary desired? <span class="userdata"><?php echo $app["salary"] ?></span>
<tr>
<td colspan=6>
Have you applied with NFS previously?<br>
(<input name="prevapplied" type="radio" value="1" <?php echo ($app["prevapplied"]==="1"?$SELECT:"") ?> >Yes)
(<input name="prevapplied" type="radio" value="0" <?php echo ($app["prevapplied"]==="0"?$SELECT:"") ?> >No)
<td colspan=6>
If so, when?
<span class="userdata"><?php echo $app["whenapplied"] ?></span>
<tr>
<td colspan=12>
Select highest grade completed:
<input type="radio" name="grade" value="9" <?php echo ($app["grade"] == "9" ? $SELECT : "") ?> >9
<input type="radio" name="grade" value="10" <?php echo ($app["grade"] == "10" ? $SELECT : "") ?> >10
<input type="radio" name="grade" value="11" <?php echo ($app["grade"] == "11" ? $SELECT : "") ?> >11
<input type="radio" name="grade" value="12" <?php echo ($app["grade"] == "12" ? $SELECT : "") ?> >12
<input type="radio" name="grade" value="equiv" <?php echo ($app["grade"] == "equiv" ? $SELECT : "") ?> >HS equivalency

<tr>
<td colspan=12>
<span class="heading">Name of School(s)</span>
<tr>
<td colspan=6>
High School
<span class="userdata"><?php echo $app["highschool"] ?></span>
<td colspan=6>
Graduated?
(<input name="hsgrad" type="radio" value="1" <?php echo ($app["hsgrad"]==="1"?$SELECT:"") ?> >Yes)
(<input name="hsgrad" type="radio" value="0" <?php echo ($app["hsgrad"]==="0"?$SELECT:"") ?> >No)
<tr>
<td colspan=6>
College or Trade School
<span class="userdata"><?php echo $app["college"] ?></span>
<td colspan=6>
Degree Earned?
(<input name="colgrad" type="radio" value="1" <?php echo ($app["colgrad"]==="1"?$SELECT:"") ?> >Yes)
(<input name="colgrad" type="radio" value="0" <?php echo ($app["colgrad"]==="0"?$SELECT:"") ?> >No)
<tr>
<td colspan=12>
<span class="heading">Military Background</span>
<tr>
<td colspan=6>
Unit and Ranking
<span class="userdata"><?php echo $app["military"] ?></span>

<tr>
<td colspan=12>
<span class="heading">Licensures</span>
<tr>
<td colspan=12 style="border:red dashed; font-weight: bold;">
A 3 Year Driving Record from the DMV must be submitted separately and in addition to
this form to complete your application.  You may fax a copy to the Hiring Coordinator at
(434) 842-9349 or send a copy by email to
<a href="mailto:careers@nationalfilterservice.com">careers@nationalfilterservice.com</a>
<tr>
<td colspan=5>
Type of license (including driver's license)
<span class="userdata"><?php echo $app["licensetype"] ?></span>
<td colspan=4>
License No.
<span class="userdata"><?php echo $app["licenseno"] ?></span>
<td colspan=3>
Issuing State
<span class="userdata"><?php echo $app["licensestate"] ?></span>

<tr>
<td colspan=12>
<span class="heading">Work Experience</span>
<tr>
<td colspan=12>
Are you currently employed?
(<input name="employed" type="radio" value="1" <?php echo ($app["employed"]==="1"?$SELECT:"") ?> >Yes)
(<input name="employed" type="radio" value="0" <?php echo ($app["employed"]==="0"?$SELECT:"") ?> >No)
<tr>
<td colspan=6>
If yes, do you need to provide your current employer notice?<br>
(<input name="neednotice" type="radio" value="1" <?php echo ($app["neednotice"]==="1"?$SELECT:"") ?> >Yes)
(<input name="neednotice" type="radio" value="0" <?php echo ($app["neednotice"]==="0"?$SELECT:"") ?> >No)
<td colspan=6>
How many weeks?
<input type="radio" name="notice" value="1" <?php echo ($app["notice"] == "1" ? $SELECT : "") ?> >One
<input type="radio" name="notice" value="2" <?php echo ($app["notice"] == "2" ? $SELECT : "") ?> >Two
</tr>
<tr>
<td>
May we contact your employer?
<input name="can_contact_emp" type="radio" value="1" <?php echo ($app["can_contact_emp"]==="1"?$SELECT:"") ?> >Yes
<input name="can_contact_emp" type="radio" value="0" <?php echo ($app["can_contact_emp"]==="0"?$SELECT:"") ?> >No
</td>
</tr>
<tr>
<td colspan=12>
<span class="emph">Please list your most recent employment first.</span>
<tr>
<td colspan=2>
Dates
<td colspan=2>
Company Name / <br/>Address/Phone Number
<td colspan=2>
Supervisor Name
<td colspan=2>
Job Title/Responsibilities
<td colspan=2>
Reason for Leaving
<td colspan=2>
Salary
<?php for($i=0; isset($jobs[$i]); $i++) {
   $j = $jobs[$i];
?>
  <tr>
  <td colspan=2>
  <pre style="font-size: 12">End:   <span class="userdata"><?php echo $j["to"] ?></span></pre>
  <pre style="font-size: 12">Start: <span class="userdata"><?php echo $j["from"] ?></span></pre>
  <td colspan=2>
  <p><span class="userdata"><?php echo $j["company"] ?></span></p>
  <td colspan=2>
  <span class="userdata"><?php echo $j["title"] ?></span>
  <td colspan=2>
  <p><span class="userdata"><?php echo $j["duties"] ?></span></p>
  <td colspan=2>
  <p><span class="userdata"><?php echo $j["reason"] ?></span></p>
  <td colspan=2>
  <p><span class="userdata"><?php echo $j["salary"] ?></span></p>
<?php
}
?>

<tr>
<td colspan=12>
For the purpose of compliance with the Immigration Reform and Control Act, are you legally
eligible for employment in the United States?
(<input name="canemploy" type="radio" value="1" <?php echo ($app["canemploy"]==="1"?$SELECT:"") ?> >Yes)
(<input name="canemploy" type="radio" value="0" <?php echo ($app["canemploy"]==="0"?$SELECT:"") ?> >No)
<tr>
<td colspan=12>
<span class="small">
Under the Immigration Reform and Control Act of 1986, you will be required to fill out a certification
verifying that you are eligible to be employed and verify your identity.  Furthere, you will be required
to provide documentation to that effect should you be employed with NFS.
</span>
</td>


<tr>
<td colspan=12>
<span class="heading">Authorization</span>
<tr>
<td colspan=12>
&quot;I hereby certify that all entries on both sides and attachments are true and complete,
and I agree and understand that any falsification of information herein, regardless of time
of discovery, may cause forfeiture on my part of any employment with National Filter Service.
I understand that all information on this application is subject to verification and I consent
to criminal history background checks.  I also consent that you may contact references, former
employers and education institutions listed regarding this application.  I further authorize
that National Filter Service to rely upon and use, as it sees fit, any information received
from such contacts.  Information contained on this application may be disseminated to other
agencies, on a need-to-know basis for good cause shown as determined by National Filter Service.&quot;
<tr>
<td colspan=4>
Date
<span class="userdata"><?php echo $app["auth_date"] ?></span>
<td colspan=8>
Application Signature
<span class="userdata"><?php echo $app["auth_sig"] ?></span>

<tr>
<td colspan=12>
<span class="heading">Electronic Signature</span>
<tr>
<td colspan=12>
<p>
Electronic Signature: Legally, an electronic signature (ex: John Doe) is any symbol executed
or adopted by a person with intent to sign the record.  By putting your name or any other characters in
the electronic signature box, you are showing your intention to sign this document.
</p>
<p>
I certify and declare under penalty of perjury under relevant state and federal law that the
information contained in my employment application is complete, true, and accurate. I
acknowledge that falsification or omission of information may result in immediate dismissal
or retraction of any offer of employment.
</p>
<p>
In consideration of National Filter Service, Inc. (herein referred to as
<span class="legalemph">EMPLOYER</span>) review of my
application for employment, I hereby voluntarily consent to and authorize
<span class="legalemph">EMPLOYER</span>, or its
authorized agents bearing this release or copy thereof, to obtain a consumer report for
employment purposes. I agree that this consumer report may include any of the following:
</p>
<p>
<ul>
<li>Employment Verification, Education Verification, Credentials Verification
<li>Personal Identity Verifications, Past Employment Verification, Reference Checks
<li>Criminal Records, Civil Cases, Motor Vehicle Records
</ul>

<p>
I authorize all persons and organizations that may have information relevant to this research
to disclose such information to <span class="legalemph">EMPLOYER</span> or its authorized agents.
I hereby release <span class="legalemph">EMPLOYER</span>,
its authorized agents, and all persons and organizations providing information from all claims
and liabilities of any nature in connection with this research. I hereby further authorize that
a photocopy of this authorization may be considered as valid as the original.
</p>

<pre>
Printed Name:         <span class="userdata"><?php echo $app["elec_name"] ?></span>
Date of Birth:        <span class="userdata"><?php echo $app["elec_birth"] ?></span>
Social Security Number:  <span class="userdata"><?php echo $app["elec_ssn"] ?></span>
Driver License &amp; State:  <span class="userdata"><?php echo $app["elec_license"] ?></span>
Signature:            <span class="userdata"><?php echo $app["elec_sig"] ?></span>
Date of Signature:    <span class="userdata"><?php echo $app["elec_date"] ?></span>
</pre>

<tr>
  <td colspan="12">
    <span class="heading">Disclosure Regarding Consumer And/or Investigative Report</span>
  </td>
</tr>

<tr>
  <td colspan="12">
    <p>
    National Filter Service ("Company") may obtain information about you for employment purposes from a third party consumer reporting agency.
    </p>

    <p>
    You have the right, upon written request made within a reasonable period of time after receipt of this notice, to request whether a consumer report has been conducted about you, disclosure of the nature and scope of any investigative consumer report, and to request a copy of your report.
    </p>

    <?php if (isset($app["cons_receive_copy"])): ?>
      <p>
      For California, Oklahoma, or Minnesota employees and applicants: Please check the appropriate box to indicate if you would like to receive a copy of your consumer report free of charge.
      </p>
      <p>
        <label for="consumer-report-receive-copy-yes"><input id="consumer-report-receive-copy-yes" name="cons_receive_copy" type="radio" value="1" <?php echo ($app["cons_receive_copy"]==="1"?$SELECT:"") ?> > Yes</label><br/>
        <label for="consumer-report-receive-copy-no"><input id="consumer-report-receive-copy-no" name="cons_receive_copy" type="radio" value="0" <?php echo ($app["cons_receive_copy"]==="0"?$SELECT:"") ?> > No</label>
      </p>
    <?php else: ?>
      <p style="color: red;">
        <strong><em><?php echo $OLD_FIELD_NOTICE ?></em></strong>
      </p>
    <?php endif ?>

    <p>
    Signature: <span class="userdata"><?php echo $app["cons_sig"] ?></span>
    </p>

    <p>
    Date: <span class="userdata"><?php echo $app["cons_date"] ?></span>
    </p>

  </td>
</tr>

<tr>
  <td colspan="12">
    <span class="heading">Acknowledgment And Authorization For Background Check</span>
  </td>
</tr>

<tr>
  <td colspan="12">
    <p>
    <input type="checkbox" id="disc_have_read" name="disc_have_read" value="<?php echo $app["disc_have_read"] ?>" <?php echo ($app["disc_have_read"]==="1"?$SELECT:"") ?> /> I have received the Disclosure Regarding Consumer and/or Investigative Report, have read and received the Summary of Your Rights, and if a California resident/applicant, the A Summary of Your Rights Under the Provisions of California Civil Code §1786.22.
    </p>

    <p>
      <input type="checkbox" id="disc_binding" name="disc_binding" value="<?php echo $app["disc_binding"] ?>" <?php echo ($app["disc_binding"]==="1"?$SELECT:"") ?> />  I understand that my signature now and throughout this process will be binding. Additionally, notices, documents, and communications may be provided electronically and will meet the requirements set forth under Federal and/or State law, as permitted by law. I agree that a facsimile ("fax"), electronic or printout of this authorization may be accepted with the same authority as the original.
    </p>
  </td>
</tr>

<tr>
  <td colspan=4>
    Print Name: <span class="userdata"><?php echo $app["disc_name"] ?></span>
  </td>

  <td colspan=8>
    Other Names Known By: <span class="userdata"><?php echo $app["disc_other_names"] ?></span>
  </td>
</tr>

<tr>
  <td colspan=6>
    Social Security Number: <span class="userdata"><?php echo $app["disc_ssn"] ?></span>
  </td>
  <td colspan=6>
    Date of Birth: <span class="userdata"><?php echo $app["disc_birth"] ?></span>
  </td>
</tr>

<tr>
  <td colspan=6>
    Driver's License Number: 
    <span class="userdata"><?php echo $app["disc_license"] ?></span>
  </td>

  <td colspan="6">
    Driver's License State: <span class="userdata"><?php echo $app["disc_license_state"] ?></span>
  </td>
</tr>

<tr>
  <td colspan=12>
    City: <span class="userdata"><?php echo $app["disc_city"] ?></span><br/>
    State: <span class="userdata"><?php echo $app["disc_state"] ?></span><br/>
    Zip: <span class="userdata"><?php echo $app["disc_zip"] ?></span><br/>
  </td>
</tr>

<tr>
  <td colspan=8>
    Signature: <span class="userdata"><?php echo $app["disc_sig"] ?></span>
  </td>

  <td colspan=4>
    Date: <span class="userdata"><?php echo $app["disc_date"] ?></span>
  </td>
</tr>

<tr>
  <td colspan="12">
    Prospective Employer: <span class="userdata"><?php echo $app["disc_employer"] ?></span>
  </td>
</tr>


</table>

<h2>Attachments</h2>
<?php
   if ($attach["attach"] != ATTACH_DONE) {
    echo "<p>The user is still able to add attachments.";
   
    if($attach["attach"] == ATTACH_PENDING) {
      echo "  The user has also said they wanted to.";
    }
   
    echo "</p>";
   }

   if($attach["count"] == 0) {
    echo "<p>The user has not attached anything.</p>";
   }
   else {
    echo "<ul>\n";
    for($i=0; $i<$attach["count"]; $i++) {
      echo  '<li>' . $attach[$i]["atype"] . ': <a href="../'. $attach[$i]["afile"] .
            '">' . $attach[$i]["adesc"] . "</a>\n";
    }
    echo "</ul>\n";
   }
?>
<form style="float:left;" method="get" action="index.php">
<div>
<input type="hidden" name="mode" value="<?php echo $status ?>">
<button type="submit">Close</button>
</div>
</form>
<?php if($status !== STATUS_EDIT) { ?>
<form style="float:right;" method="post" action="index.php">
<div>
<input type="hidden" name="mode" value="<?php echo htmlentities($status); ?>">
<input type="hidden" name="aid" value="<?php echo $app["aid"]?>">
<input type="hidden" name="page" value="admin_list">
<input type="hidden" name="action" value="unsubmit">
<button type="submit">unsubmit</button>
</div>
</form>
<?php } if($status !== STATUS_DONE) { ?>
<form style="float:right;" method="post" action="index.php">
<div>
<input type="hidden" name="mode" value="<?php echo htmlentities($status); ?>">
<input type="hidden" name="aid" value="<?php echo $app["aid"]?>">
<input type="hidden" name="page" value="admin_list">
<input type="hidden" name="action" value="delete">
<button type="submit">delete</button>
</div>
</form>
<?php }

   include("pagewrap.inc.php");
?>


