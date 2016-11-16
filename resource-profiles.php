<?php include('includes/header.php') ?>

  <div class="standards-page">

    <h1 class="page-heading">FHIR resource profiles</h1>

    <h3>Overview</h3>

    <p>This section references profile collections that have been submitted by INTEROPen members or member groups for consideration and further validation by other stakeholders.</p> 
    <p>Any group member or group of members can submit members proposals subject to the following:</p>

    <ol>
      <li>They should be created using the Forge tool.</li>
      <li>They must first be submitted via a pull request to the <a href="https://github.com/HL7-UK/CareConnect-profiles">HL7 CareConnect repository</a> to ensure visibility and access to the source.</li>
      <li>A suitable view of the FHIR profile (e.g. Simplifier viewer, or via clinFHIR) should be created in order to be able to view the profiles, together with any documentation providing rationale for changes to the current version.</li>
      <li>A submission to this web site should include a summary of the project content and the organisations responsible for editing the proposal.</li>
    </ol>
    
    <h3>Member proposals</h3>
    
    <ol>
      <li><a href="candidate-profiles/care-connect/"><span style="font-size: 120%">The CareConnect Group</span></a>
        <div style="padding-top: 10px;">Available for preview (<a href="candidate-profiles/care-connect/">HERE</a>):</div>
        <ul class="candidate-profile-name-list">
          <li>AllergyIntolerance</li>
          <li>Condition</li>
          <li>MedicationOrder</li>
          <li>MedicationStatement</li>
          <li>Observation</li>
          <li>Patient</li>
        </ul>
        
        <div style="padding-top: 10px;">Under development:</div>
        <ul class="candidate-profile-name-list">
          <li>Encounter</li>
          <li>Immunization</li>
          <li>VitalSigns</li>
        </ul>
      </li>
    </ol>
    
    <h3>Other resouces</h3>
    
    <p>External FHIR resources relevant to UK health and social:</p>
    
    <ul>    
      <li><a href="https://nhsconnect.github.io/gpconnect/index.html">GP-Connect – NHS Digital program for providing a set of APIs into GP system</a></li>

      <li><a href="http://theprsb.org/publications/bible-sets-out-the-latest-agreed-standards">PRSB - Standards for the clinical structure and content of patient records</a></li>
    </ul>
      
  </div>

<?php include('includes/footer.php') ?>