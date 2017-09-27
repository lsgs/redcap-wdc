<?php

/**
 * Tableau - REDCap Web Data Connector
 * Luke Stevens, Murdoch Children's Research Institute
 * From fork of https://github.com/xHeliotrope/redcap-wdc
 */

define('NOAUTH', true);
require '../../redcap_connect.php';

$page = new HtmlPage();
$page->PrintHeaderExt();
?>
  <div class="container-fluid">
    <div class="row">
      <div class="" style="margin:0 -15px">
        <h1 style="padding-bottom:20px;vertical-align:middle;">
          REDCap Web Data Connector for Tableau
        </h1>
      </div>
    </div>
    <div class="row">
      <div class="" style="padding-bottom:10px; padding-top:30px">
        <p>Submit your API Token to connect to your REDCap Project Data to Tableau.</p>
        <div class="form-group">
          <input type="hidden" id="url" value="<?php echo APP_PATH_WEBROOT_FULL.'api/';?>">
          <label for="usr">API Token:</label>
          <input type="text" class="form-control" id="token" placeholder="ABCDEF...">
        </div>
        <button class="btn btn-default" id="submitButton" type="button">Submit</button>
      </div>
    </div>
  </div>
  <script src="https://connectors.tableau.com/libs/tableauwdc-2.0.8.min.js" type="text/javascript"></script>
  <script>

  (function() {

      var myConnector = tableau.makeConnector();

      // Define the schema
      //  myConnector.getSchema = function(schemaCallback){}

        myConnector.getSchema = function(schemaCallback) {
          var recordsInfo = [];
          $.ajax({
            url: JSON.parse(tableau.connectionData)['url'],
            type: "POST",
            data: {
              token: JSON.parse(tableau.connectionData)['token'],
              content: 'exportFieldNames',
              format: 'json',
              returnFormat: 'json',
              type: 'flat',
              rawOrLabelHeaders: 'raw',
              exportCheckboxLabel: 'true',
              exportSurveyFields: 'true',
              exportDataAccessGroups: 'true'
              },
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            success: function(resp){
                recordsInfo = resp;
                var recordSchema = [];
                recordsInfo.forEach(function(field){
                  recordSchema.push({
                    id: field.export_field_name,
                    alias: field.original_field_name,
                    dataType: tableau.dataTypeEnum.string
                  });
                });
                var redcapTable = {
                  id: "redcap",
                  alias: "custom redcap extract",
                  columns: recordSchema
                }
                schemaCallback([redcapTable]);
              }
            });
         };


      // Download the data
      myConnector.getData = function(table, doneCallback) {

        var tableData = [];
          $.ajax({
            url: JSON.parse(tableau.connectionData)['url'],
            type: "POST",
            data: {
              token: JSON.parse(tableau.connectionData)['token'],
              content: 'record',
              format: 'json',
              returnFormat: 'json',
              type: 'flat',
              rawOrLabelHeaders: 'raw',
              exportCheckboxLabel: 'true',
              exportSurveyFields: 'true',
              exportDataAccessGroups: 'true'
            },
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            success: function(resp){
            resp.forEach(function(record){
              tableData.push(record);
            });
            table.appendRows(tableData);
            doneCallback();
          }
        });
        }

      tableau.registerConnector(myConnector);

      $(document).ready(function (){
        $("#submitButton").click(function() {
            tableau.connectionData = JSON.stringify({
              'token': $("#token").val(),
              'url': $("#url").val()
            });
            tableau.connectionName = "REDCap Data";
            tableau.submit();
        });
      });

  })();
</script>
</body>
</html>
