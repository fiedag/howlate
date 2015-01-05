<?php
header('Content-type: application/xml');
header('Content-disposition: attachment; filename=HowLateAgent.exe.config');
echo '<?xml version="1.0" encoding="utf-8" ?>';
?><configuration>
  <configSections>
    <sectionGroup name="applicationSettings" type="System.Configuration.ApplicationSettingsGroup, System, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" >
      <section name="com.howlate.Properties.Settings" type="System.Configuration.ClientSettingsSection, System, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089" requirePermission="false" />
    </sectionGroup>
    <section name="log4net"
         type="log4net.Config.Log4NetConfigurationSectionHandler, log4net"
         requirePermission="false"/>
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
      <setting name="URL" serializeAs="String">
        <value><?php echo $url;?></value>
      </setting>
      <setting name="Credentials" serializeAs="String">
        <value><?php echo $credentials;?></value>
      </setting>
      <setting name="PollIntervalSeconds" serializeAs="String">
        <value><?php echo $interval;?></value>
      </setting>
      <setting name="SelectSessions" serializeAs="String">
        <value>select * from SESSION where 1 = 0</value>
      </setting>        
      <setting name="SelectLates" serializeAs="String">
        <value>select a1.PractitionerID, [Name] As Provider, a1.[When] As AppointmentTime, a1.[TimeInWAITROOM] As ArrivalTime, a1.TimeInConsult As ConsultationTime,
DATEDIFF(minute,TimeInWAITROOM, TimeInConsult) As NewLate,
2 /* hours */ As Horizon
from APPT a1, PRAC
where a1.PractitionerID = PRAC.PractitionerID 
and DATEADD(dd, 0, DATEDIFF(dd, 0, [When])) = DATEADD(dd, 0, DATEDIFF(dd, 0, GETDATE()))  -- today some time
and TimeInConsult =   /* latest consult for this practitioner */
(SELECT MAX(TimeInConsult) FROM APPT a2
 WHERE a2.PractitionerID = a1.PractitionerID
 and DATEADD(dd, 0, DATEDIFF(dd, 0, a1.[When])) = DATEADD(dd, 0, DATEDIFF(dd, 0, a2.[When])) 
 and TimeInConsult is not null  -- consult has begun
)
and DATEPART(hour,GETDATE()) &lt;= 
  (SELECT MAX(DATEPART(hour,b2.[1End]) + 2)   -- no later than 2 hours after end of session
   FROM SESSION b2
   WHERE b2.DayOfWk = DATEPART(weekday,GETDATE())
   AND (b2.PractitionerID = 0 or b2.PractitionerID = a1.PractitionerID)
  )
</value>
      </setting>
      <setting name="Credentials" serializeAs="String">
        <value>admin.9cbf8a4dcb8e30682b927f352d6559a0</value>
      </setting>
      <setting name="SelectToNotify" serializeAs="String">
        <value>select a1.PatientID, p.SURNAME, a1.[When] As AppointmentDate, p.PHONE_MOBILE As MobilePhone from APPT a1
inner join CM_PATIENT p on p.PATIENT_ID = a1.PatientID
where a1.TimeInWAITROOM is null and a1.PractitionerID = @PractitionerID and p.PHONE_MOBILE &lt;&gt; ''
and a1.[When] &gt; @AppointmentTime and a1.[When] &lt; DATEADD(hour, @Horizon, @AppointmentTime)

</value>
      </setting>
      <setting name="PMS" serializeAs="String">
        <value>MedicalDirector</value>
      </setting>
    </com.howlate.Properties.Settings>
    
  </applicationSettings>

  <log4net>
    <appender name="ColoredConsoleAppender"
              type="log4net.Appender.ColoredConsoleAppender">
      <target value="Console.Error" />
      <mapping>
        <level value="FATAL" />
        <foreColor value="Red" />
        <backColor value="White" />
      </mapping>
      <mapping>
        <level value="ERROR" />
        <foreColor value="Red, HighIntensity" />
      </mapping>
      <mapping>
        <level value="WARN" />
        <foreColor value="Yellow" />
      </mapping>
      <mapping>
        <level value="INFO" />
        <foreColor value="Cyan" />
      </mapping>
      <mapping>
        <level value="DEBUG" />
        <foreColor value="Green" />
      </mapping>
      <layout type="log4net.Layout.SimpleLayout" />
    </appender>
    <appender name="RollingFileAppender"
              type="log4net.Appender.RollingFileAppender">
      <file value="HowLateAgent.log" />
      <appendToFile value="true" />
      <rollingStyle value="Size" />
      <maxSizeRollBackups value="10" />
      <maximumFileSize value="10MB" />
      <staticLogFileName value="true" />
      <layout type="log4net.Layout.SimpleLayout" />
    </appender>
    <!-- 
      
      For more examples of appenders see: 
      http://www.beefycode.com/post/Log4Net-Tutorial-pt-3-Appenders.aspx 
      
      -->
    <root>
      <level value="ALL"/>
      <appender-ref ref="ColoredConsoleAppender"/>
      <appender-ref ref="RollingFileAppender"/>
    </root>
  </log4net>



</configuration>