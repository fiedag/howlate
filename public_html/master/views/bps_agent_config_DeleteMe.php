<?php
header('Content-type: application/xml');
header('Content-disposition: attachment; filename=HowLateAgent.exe.config');
echo '<?xml version="1.0" encoding="utf-8" ?>';
?><configuration>
  <configSections>
    <sectionGroup name="applicationSettings" type="System.Configuration.ApplicationSettingsGroup, System, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" >
      <section name="com.howlate.Properties.Settings" type="System.Configuration.ClientSettingsSection, System, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" requirePermission="false" />
    </sectionGroup>
  </configSections>
  <applicationSettings>
    <com.howlate.Properties.Settings>
      <setting name="Subdomain" serializeAs="String">
        <value><?php echo $subdomain;?></value>
      </setting>
      <setting name="Clinic" serializeAs="String">
        <value><?php echo $clinic;?></value>
      </setting>
      <setting name="Instance" serializeAs="String">
        <value><?php echo $instance;?></value>
      </setting>
      <setting name="Database" serializeAs="String">
        <value><?php echo $database;?></value>
      </setting>
      <setting name="UID" serializeAs="String">
        <value><?php echo $uid;?></value>
      </setting>
      <setting name="PWD" serializeAs="String">
        <value><?php echo $pwd;?></value>
      </setting>
      <setting name="URL" serializeAs="String">
        <value><?php echo $url;?></value>
      </setting>
      <setting name="Credentials" serializeAs="String">
        <value><?php echo $credentials;?></value>
      </setting>
      <setting name="PollIntervalSeconds" serializeAs="String">
        <value><?php echo $interval;?></value>
      </setting>
      <setting name="SelectLates" serializeAs="String">
          <value>select a1.InternalID, a1.Status, a1.ArrivalTime, a1.AppointmentDate, a1.AppointmentTime, a1.ConsultationTime, 7200 As Horizon 
from BPS_Appointments a1, BPS_Sessions b1 
where a1.AppointmentDate = DATEADD(dd, 0, DATEDIFF(dd, 0, GETDATE()))
and a1.ConsultationTime = 
(SELECT MAX(a2.ConsultationTime) FROM BPS_Appointments a2 
WHERE a2.AppointmentDate = a1.AppointmentDate and a2.Provider 
= a1.Provider and a2.ConsultationTime != 0)
and a1.Provider = b1.Provider
and b1.Day = datename(dw,getdate())
and b1.EndTime + 7200 &gt; DATEDIFF(s, 0,DATEADD(Day, 0 - DATEDIFF(Day, 0, getdate()), getdate()))
          
</value>
      </setting>
      <setting name="SelectSessions" serializeAs="String">
        <value>select * from BPS_Sessions where Provider &lt;&gt; ''</value>
      </setting>        
      <setting name="SelectToNotify" serializeAs="String">
        <value>select a1.Patient, a1.InternalID, a1.AppointmentDate, a1.AppointmentTime, a1.Provider, p.MobilePhone from BPS_Appointments a1
inner join BPS_Patients p on p.InternalID = a1.InternalID
inner join PATIENTS ON PATIENTS.INTERNALID = p.InternalID
where a1.ArrivalTime = 0 and a1.Provider = @Provider and p.MobilePhone &lt;&gt; '' and PATIENTS.CONSENTSMSREMINDER = 1
and (
 (a1.AppointmentDate = @AppointmentDate
and a1.AppointmentTime &gt; @AppointmentTime
and a1.AppointmentTime &lt;= @AppointmentTime + @Horizon)
 or
 (@AppointmentTime &gt; (84600 - @Horizon) and a1.AppointmentDate = DATEADD(day,1,@AppointmentDate) 
  and a1.AppointmentTime &lt; @AppointmentTime - 86400 + @Horizon
 )
)
</value>
      </setting>
      <setting name="ProcessRecalls" serializeAs="String">
        <value><?php echo ($processrecalls)?"True":"False"; ?></value>
      </setting>        

      <setting name="PMS" serializeAs="String">
        <value>BestPractice</value>
      </setting>
    </com.howlate.Properties.Settings>

  </applicationSettings>
</configuration>

