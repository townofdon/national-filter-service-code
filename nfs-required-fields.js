// Check if the element found (if any) has a value of "1"
function yes(id) { return Validator.rEqual(id,"1"); }

// pattern match a string to see if it looks like a(n) HTTP URL
function webaddr(id) {
  return [id, function(id) { return /^\s*http:\/\//.test(this.value(id)); }];
};

// match only a selected radio button
function match_radio(e) {
  return e.tagName == "INPUT" && e.type=="radio" && e.checked;
}


var hearsrc_override = {
     focus: function(id) {
       Validator.def_focus("hearsrc_other");
     },
     mark: function(id) {        
       Validator.def_mark("hearsrc_source");
     },
     unmark: function(id) {
       Validator.def_unmark("hearsrc_source");
     }
};





var validity_reqs = {
   req_each : [
    "firstname", "lastname", "street", "city", "state", "zip",
    "posname", "hearsrc_source", "salary",
    "licensetype", "licenseno", "licensestate",
    "auth_date", "auth_sig",
    "elec_name", "elec_birth", "elec_license", "elec_sig", "elec_date",
    "grade",
    /* the following fields have been added by Chiedo Labs - https://labs.chiedo.com */
    "cons_sig", "cons_date", "disc_have_read", "disc_binding", "disc_name", "disc_ssn", "disc_birth",
    "disc_license", "disc_license_state", "disc_city", "disc_state", "disc_zip", "disc_sig", "disc_date"
   ],

   req_any : [
    ["homeph", "cellph"]
   ],

   req_if : [
    // [ cond, [thenA, thenB, ...] ]
     
    [yes("neednotice"), ["notice"]],
    [yes("colgrad"), ["college"]],
    [yes("hsgrad"), ["highschool"]],
    [yes("prevapplied"), ["whenapplied"]],
    [yes("employed"), ["neednotice"]],
    [Validator.rEqual("posname", "other"), ["posdesc"]],
     
    [Validator.rEqual("hearsrc_source", "newspaper"), ["hearsrc_newspaper"]],
    [Validator.rEqual("hearsrc_source", "radio"),   ["hearsrc_radio"]],
    [Validator.rEqual("hearsrc_source", "online"),  ["hearsrc_online"]],
    [Validator.rEqual("hearsrc_source", "other"),   ["hearsrc_other"]]
     
   ],

   req_alike : [
    [yes("convicted"), "convdate", "convplace", "convdesc"],
    ["ref1_name", "ref1_title", "ref1_phone"],
    ["ref2_name", "ref2_title", "ref2_phone"],
    ["ref3_name", "ref3_title", "ref3_phone"]
  ],

  override: {
      // override value function for hearsrc_source so it only tests radio buttons
      hearsrc_source : {
          value: function(id) {
              var e = Validator.search(id, match_radio);
              if(e == null) return null;
              return e.value;
             
          }
      },

      // cause hearsrc_* marking to apply to the whole hearsrc_source set
      hearsrc_newspaper : hearsrc_override,
      hearsrc_radio : hearsrc_override,
      hearsrc_online : hearsrc_override,
      hearsrc_other : hearsrc_override
  }
};



// add Job requirements for each job extant in document
function loadJobReqs() {
   var i,j;
   var jc = [
    "to",
    "from",
    "company",
    "title",
    "duties",
    "reason",
    "salary"
   ];
   for(i=0;document.getElementById("to"+i);i++) {
    var a = [];
    for(j=0;j<jc.length;j++) {
      a.push(jc[j]+i);
    }
    validity_reqs.req_alike.push(a);
   }
};

// Add to onclick handler for submit element after window loads.
window.onload = function() {
   var validator;
   loadJobReqs();
   validator = new Validator(validity_reqs);
   document.getElementById("submit").onclick =
    function() {
      return validator.run();
    };
};



