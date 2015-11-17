<?php
header('Content-type: application/xml');
header('Content-disposition: attachment; filename=HowLateAgent.exe.config');
echo '<?xml version="1.0" encoding="utf-8" ?>' . "\r";
?>
<configuration>
  <configSections>
    <sectionGroup name="applicationSettings" type="System.Configuration.ApplicationSettingsGroup, System, Version=2.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" >
      <section name="com.howlate.Properties.Settings" type="System.Configuration.ClientSettingsSection, System, Version=2.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" requirePermission="false" />
    </sectionGroup>
  </configSections>
  <applicationSettings>
    <com.howlate.Properties.Settings>
      <setting name="OrgID" serializeAs="String">
        <value><?php echo $record->OrgID;?></value>
      </setting>
      <setting name="ClinicID" serializeAs="String">
        <value><?php echo $record->ClinicID;?></value>
      </setting>
      <setting name="URL" serializeAs="String">
        <value><?php echo $URL;?></value>
      </setting>
      <setting name="Credentials" serializeAs="String">
        <value><?php echo $Credentials;?></value>
      </setting>
      <setting name="Platform" serializeAs="String">
        <value><?php echo $record->Platform;?></value>
      </setting>
      <setting name="ConnectionType" serializeAs="String">
        <value><?php echo $record->ConnectionType;?></value>
      </setting>
      <setting name="ConnectionString" serializeAs="String">
        <value><?php echo $record->ConnectionString;?></value>
      </setting>
      <setting name="PollIntervalSeconds" serializeAs="String">
        <value><?php echo $record->PollInterval;?></value>
      </setting>
      <setting name="SelectSessions" serializeAs="String">
          <value><?php echo str_replace(">","&gt;",str_replace("<","&lt;", $record->SelectSessions));?></value>
      </setting>        
      <setting name="SelectAppointments" serializeAs="String">
          <value><?php echo str_replace(">","&gt;",str_replace("<","&lt;", $record->SelectAppointments));?></value>
      </setting>
      <setting name="SelectTimeNow" serializeAs="String">
          <value><?php echo str_replace(">","&gt;",str_replace("<","&lt;", $record->SelectTimeNow));?></value>
      </setting>
      <setting name="SelectApptTypes" serializeAs="String">
          <value><?php echo str_replace(">","&gt;",str_replace("<","&lt;", $record->SelectApptTypes));?></value>
      </setting>
      <setting name="SelectApptStatus" serializeAs="String">
          <value><?php echo str_replace(">","&gt;",str_replace("<","&lt;", $record->SelectApptStatus));?></value>
      </setting>
    </com.howlate.Properties.Settings>

  </applicationSettings>
</configuration>