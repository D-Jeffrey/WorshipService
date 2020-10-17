# WorshipService
Based on many years of nudging it along and keeping it running, this is a compose of many projects of work.  The code based should be refactored, and some white labeling is required.

Working to make this a white labelled system.  It continue many features build to provide support for organizing the worship service.  
The orginal code based was assemebled by Rob Klapwyk. 
- Calendar view with team available for scheduleing worship members
- Song order, sheet music links or attachments
- Scheduling of different roles within the service
- Time for Pratice
- members maagement with roles
- communcations about service and pratices times, with associated schedule
- swap and request between team members to trading schedule times for roles
- song sheet database to rate music
- generate music booked based on the songs in the database
- adjust the key of music

This release runs on PHP 7.1 The database was build for MySQL, but support any database background.
E-mail notification is sent thru SMTP.

There are two configuration files.
- lr/config.php is the primary one
- and for now fnSmtp.php needs to be configurated until the code is refactored
- additionaly there are a number of branding graphics that should be replaced in /css/images/ and /images/PDF
