; <?php exit; // DO NOT DELETE?>
; DO NOT DELETE THE ABOVE LINE!!!
; Doing so will expose this configuration file through your web site!
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
;
; config.TEMPLATE.inc.php
;
; Copyright (c) 2014-2021 Simon Fraser University
; Copyright (c) 2003-2021 John Willinsky
; Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
;
; OMP Configuration settings.
; Rename config.TEMPLATE.inc.php to config.inc.php to use.
;
;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;


;;;;;;;;;;;;;;;;;;;;
; General Settings ;
;;;;;;;;;;;;;;;;;;;;

[general]

; Set this to On once the system has been installed
; (This is generally done automatically by the installer)
installed = Off

; The canonical URL to the OMP installation (excluding the trailing slash)
base_url = "https://pkp.sfu.ca/omp"

; Enable strict mode. This will more aggressively cause errors/warnings when
; deprecated behaviour exists in the codebase.
strict = Off

; Session cookie name
session_cookie_name = OMPSID

; Session cookie path; if not specified, defaults to the detected base path
; session_cookie_path = /

; Number of days to save login cookie for if user selects to remember
; (set to 0 to force expiration at end of current session)
session_lifetime = 30

; Enable support for running scheduled tasks
; Set this to On if you have set up the scheduled tasks script to
; execute periodically
scheduled_tasks = Off

; Scheduled tasks will send email about processing
; only in case of errors. Set to off to receive
; all other kind of notification, including success,
; warnings and notices.
scheduled_tasks_report_error_only = On

; Site time zone
; Please refer to https://www.php.net/timezones for a full list of supported
; time zones.
; I.e.: "Europe/Amsterdam"
; time_zone="Europe/Amsterdam"
time_zone = "UTC"

; Short and long date formats
date_format_short = "Y-m-d"
date_format_long = "F j, Y"
datetime_format_short = "Y-m-d h:i A"
datetime_format_long = "F j, Y - h:i A"
time_format = "h:i A"

; Use URL parameters instead of CGI PATH_INFO. This is useful for broken server
; setups that don't support the PATH_INFO environment variable.
; WARNING: This option is DEPRECATED and will be removed in the future.
disable_path_info = Off

; Use fopen(...) for URL-based reads. Modern versions of dspace
; will not accept requests using fopen, as it does not provide a
; User Agent, so this option is disabled by default. If this feature
; is disabled by PHP's configuration, this setting will be ignored.
allow_url_fopen = Off

; Base URL override settings: Entries like the following examples can
; be used to override the base URLs used by OMP. If you want to use a
; proxy to rewrite URLs to OMP, configure your proxy's URL with this format.
; Syntax: base_url[press_path] = http://www.example.com
;
; Example1: URLs that aren't part of a particular press.
;    Example1: base_url[index] = http://www.example.com
; Example2: URLs that map to a subdirectory.
;    Example2: base_url[myPress] = http://www.example.com/myPress
; Example3: URLs that map to a subdomain.
;    Example3: base_url[myOtherPress] = http://myOtherPress.example.com

; Generate RESTful URLs using mod_rewrite.  This requires the
; rewrite directive to be enabled in your .htaccess or httpd.conf.
; See FAQ for more details.
restful_urls = Off

; Restrict the list of allowed hosts to prevent HOST header injection.
; See docs/README.md for more details. The list should be JSON-formatted.
; An empty string indicates that all hosts should be trusted (not recommended!)
; Example:
; allowed_hosts = '["myjournal.tld", "anotherjournal.tld", "mylibrary.tld"]'
allowed_hosts = ''

; Allow the X_FORWARDED_FOR header to override the REMOTE_ADDR as the source IP
; Set this to "On" if you are behind a reverse proxy and you control the
; X_FORWARDED_FOR header.
; Warning: This defaults to "On" if unset for backwards compatibility.
trust_x_forwarded_for = Off

; Set the following parameter to off if you want to work with the uncompiled
; (non-minified) JavaScript source for debugging or if you are working off a
; development branch without compiled JavaScript.
enable_minified = Off

; Provide a unique site ID and OAI base URL to PKP for statistics and security
; alert purposes only.
enable_beacon = On

; The number of days a new user has to validate their account
; A new user account will be expired and removed if this many days have passed since the user registered
; their account, and they have not validated their account or logged in. If the user_validation_period is set to
; 0, unvalidated accounts will never be removed. Use this setting to automatically remove bot registrations.
user_validation_period = 28


;;;;;;;;;;;;;;;;;;;;;
; Database Settings ;
;;;;;;;;;;;;;;;;;;;;;

[database]

driver = mysqli
host = localhost
username = omp
password = omp
name = omp

; Set the non-standard port and/or socket, if used
; port = 3306
; unix_socket = /var/run/mysqld/mysqld.sock

; Database collation
; collation = utf8_general_ci

; Enable database debug output (very verbose!)
debug = Off

;;;;;;;;;;;;;;;;;;
; Cache Settings ;
;;;;;;;;;;;;;;;;;;

[cache]

; The type of data caching to use. Options are:
; - memcache: Use the memcache server configured below
; - file: Use file-based caching; configured below
; - none: Use no caching. This may be extremely slow.
; This setting affects locale data, press settings, and plugin settings.

cache = file

; Enable memcache support
memcache_hostname = localhost
memcache_port = 11211

; For site visitors who are not logged in, many pages are often entirely
; static (e.g. About, the home page, etc). If the option below is enabled,
; these pages will be cached in local flat files for the number of hours
; specified in the web_cache_hours option. This will cut down on server
; overhead for many requests, but should be used with caution because:
; 1) Things like press metadata changes will not be reflected in cached
;    data until the cache expires or is cleared, and
; 2) This caching WILL NOT RESPECT DOMAIN-BASED SUBSCRIPTIONS.
; However, for situations like hosting high-volume open access presses, it's
; an easy way of decreasing server load.
;
; When using web_cache, configure a tool to periodically clear out cache files
; such as CRON. For example, configure it to run the following command:
; find .../ojs/cache -maxdepth 1 -name wc-\*.html -mtime +1 -exec rm "{}" ";"
web_cache = Off
web_cache_hours = 1


;;;;;;;;;;;;;;;;;;;;;;;;;
; Localization Settings ;
;;;;;;;;;;;;;;;;;;;;;;;;;

[i18n]

; Default locale
locale = en_US

; Client output/input character set
client_charset = utf-8

; Database connection character set
connection_charset = utf8


;;;;;;;;;;;;;;;;;
; File Settings ;
;;;;;;;;;;;;;;;;;

[files]

; Complete path to directory to store uploaded files
; (This directory should not be directly web-accessible)
; Windows users should use forward slashes
files_dir = files

; Path to the directory to store public uploaded files
; (This directory should be web-accessible and the specified path
; should be relative to the base OMP directory)
; Windows users should use forward slashes
public_files_dir = public

; The maximum allowed size in kilobytes of each user's public files
; directory. This is where user's can upload images through the
; tinymce editor to their bio. Editors can upload images for
; some of the settings.
; Set this to 0 to disallow such uploads.
public_user_dir_size = 5000

; Permissions mask for created files and directories
umask = 0022

; The minimum percentage similarity between filenames that should be considered
; a possible revision
filename_revision_match = 70

;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Fileinfo (MIME) Settings ;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;

[finfo]
; mime_database_path = /etc/magic.mime


;;;;;;;;;;;;;;;;;;;;;
; Security Settings ;
;;;;;;;;;;;;;;;;;;;;;

[security]

; Force SSL connections site-wide
force_ssl = Off

; Force SSL connections for login only
force_login_ssl = Off

; This check will invalidate a session if the user's IP address changes.
; Enabling this option provides some amount of additional security, but may
; cause problems for users behind a proxy farm (e.g., AOL).
session_check_ip = Off

; The encryption (hashing) algorithm to use for encrypting user passwords
; Valid values are: md5, sha1
; NOTE: This hashing method is deprecated, but necessary to permit gradual
; migration of old password hashes.
encryption = sha1

; The unique salt to use for generating password reset hashes
salt = "YouMustSetASecretKeyHere!!"

; The unique secret used for encoding and decoding API keys
api_key_secret = ""

; The number of seconds before a password reset hash expires (defaults to
; 7200 seconds (2 hours)
reset_seconds = 7200

; Allowed HTML tags for fields that permit restricted HTML.
; Use e.g. "img[src,alt],p" to allow "src" and "alt" attributes to the "img"
; tag, and also to permit the "p" paragraph tag. Unspecified attributes will be
; stripped.
allowed_html = "a[href|target|title],em,strong,cite,code,ul,ol,li[class],dl,dt,dd,b,i,u,img[src|alt],sup,sub,br,p"

;Is implicit authentication enabled or not

;implicit_auth = On

;Implicit Auth Header Variables

;implicit_auth_header_first_name = HTTP_TDL_GIVENNAME
;implicit_auth_header_last_name = HTTP_TDL_SN
;implicit_auth_header_email = HTTP_TDL_MAIL
;implicit_auth_header_phone = HTTP_TDL_TELEPHONENUMBER
;implicit_auth_header_initials = HTTP_TDL_METADATA_INITIALS
;implicit_auth_header_mailing_address = HTTP_TDL_METADATA_TDLHOMEPOSTALADDRESS
;implicit_auth_header_uin = HTTP_TDL_TDLUID

; A space delimited list of uins to make admin
;implicit_auth_admin_list = "100000040@tdl.org 85B7FA892DAA90F7@utexas.edu 100000012@tdl.org"

; URL of the implicit auth 'Way Finder' page. See pages/login/LoginHandler.php for usage.

;implicit_auth_wayf_url = "/Shibboleth.sso/wayf"



;;;;;;;;;;;;;;;;;;
; Email Settings ;
;;;;;;;;;;;;;;;;;;

[email]

; Default method to send emails
; Available options: sendmail, smtp, log, phpmailer
default = sendmail

; Path to the sendmail, -bs argument is for using SMTP protocol
sendmail_path = "/usr/sbin/sendmail -bs"

; Use SMTP for sending mail instead of mail()
; smtp = On

; SMTP server settings
; smtp_server = mail.example.com
; smtp_port = 25

; Enable SMTP authentication
; Supported smtp_auth: ssl, tls (see PHPMailer SMTPSecure)
; smtp_auth = ssl
; smtp_username = username
; smtp_password = password
;
; Supported smtp_authtype: RAM-MD5, LOGIN, PLAIN, XOAUTH2 (see PHPMailer AuthType)
; (Leave blank to try them in that order)
; smtp_authtype =

; The following are required for smtp_authtype = XOAUTH2 (e.g. GMail OAuth)
; (See https://github.com/PHPMailer/PHPMailer/wiki/Using-Gmail-with-XOAUTH2)
; smtp_oauth_provider = Google
; smtp_oauth_email =
; smtp_oauth_clientid =
; smtp_oauth_clientsecret =
; smtp_oauth_refreshtoken =

; Enable suppressing verification of SMTP certificate in PHPMailer
; Note: this is not recommended per PHPMailer documentation
; smtp_suppress_cert_check = On

; Allow envelope sender to be specified
; (may not be possible with some server configurations)
; allow_envelope_sender = Off

; Default envelope sender to use if none is specified elsewhere
; default_envelope_sender = my_address@my_host.com

; Force the default envelope sender (if present)
; This is useful if setting up a site-wide noreply address
; The reply-to field will be set with the reply-to or from address.
; force_default_envelope_sender = Off

; Force a DMARC compliant from header (RFC5322.From)
; If any of your users have email addresses in domains not under your control
; you may need to set this to be compliant with DMARC policies published by
; those 3rd party domains.
; Setting this will move the users address into the reply-to field and the
; from field wil be rewritten with the default_envelope_sender.
; To use this you must set force_default_enveloper_sender = On and
; default_envelope_sender must be set to a valid address in a domain you own.
; force_dmarc_compliant_from = Off

; The display name to use with a DMARC compliant from header
; By default the DMARC compliant from will have an empty name but this can
; be changed by adding a text here.
; You can use '%n' to insert the users name from the original from header
; and '%s' to insert the localized sitename.
; dmarc_compliant_from_displayname = '%n via %s'

; Amount of time required between attempts to send non-editorial emails
; in seconds. This can be used to help prevent email relaying via OMP.
time_between_emails = 3600

; Maximum number of recipients that can be included in a single email
; (either as To:, Cc:, or Bcc: addresses) for a non-priveleged user
max_recipients = 10

; If enabled, email addresses must be validated before login is possible.
require_validation = Off

; Maximum number of days before an unvalidated account expires and is deleted
validation_timeout = 14


;;;;;;;;;;;;;;;;;;;
; Search Settings ;
;;;;;;;;;;;;;;;;;;;

[search]

; Minimum indexed word length
min_word_length = 3

; The maximum number of search results fetched per keyword. These results
; are fetched and merged to provide results for searches with several keywords.
results_per_keyword = 500

; The number of hours for which keyword search results are cached.
result_cache_hours = 1

; Paths to helper programs for indexing non-text files.
; Programs are assumed to output the converted text to stdout, and "%s" is
; replaced by the file argument.
; Note that using full paths to the binaries is recommended.
; Uncomment applicable lines to enable (at most one per file type).
; Additional "index[MIME_TYPE]" lines can be added for any mime type to be
; indexed.

; PDF
; index[application/pdf] = "/usr/bin/pstotext -enc UTF-8 -nopgbrk %s - | /usr/bin/tr '[:cntrl:]' ' '"
; index[application/pdf] = "/usr/bin/pdftotext -enc UTF-8 -nopgbrk %s - | /usr/bin/tr '[:cntrl:]' ' '"

; PostScript
; index[application/postscript] = "/usr/bin/pstotext -enc UTF-8 -nopgbrk %s - | /usr/bin/tr '[:cntrl:]' ' '"
; index[application/postscript] = "/usr/bin/ps2ascii %s | /usr/bin/tr '[:cntrl:]' ' '"

; Microsoft Word
; index[application/msword] = "/usr/bin/antiword %s"
; index[application/msword] = "/usr/bin/catdoc %s"


;;;;;;;;;;;;;;;;
; OAI Settings ;
;;;;;;;;;;;;;;;;

[oai]

; Enable OAI front-end to the site
oai = On

; OAI Repository identifier
repository_id = omp.pkp.sfu.ca


;;;;;;;;;;;;;;;;;;;;;;
; Interface Settings ;
;;;;;;;;;;;;;;;;;;;;;;

[interface]

; Number of items to display per page; overridable on a per-press basis
items_per_page = 50

; Number of page links to display; overridable on a per-press basis
page_links = 10


;;;;;;;;;;;;;;;;;;;;
; Captcha Settings ;
;;;;;;;;;;;;;;;;;;;;

[captcha]

; Whether or not to enable ReCaptcha
recaptcha = off

; Public key for reCaptcha (see http://www.google.com/recaptcha)
; recaptcha_public_key = your_public_key

; Private key for reCaptcha (see http://www.google.com/recaptcha)
; recaptcha_private_key = your_private_key

; Whether or not to use Captcha on user registration
captcha_on_register = on

; Whether or not to use Captcha on user login
captcha_on_login = on

; Validate the hostname in the ReCaptcha response
recaptcha_enforce_hostname = Off

;;;;;;;;;;;;;;;;;;;;;
; External Commands ;
;;;;;;;;;;;;;;;;;;;;;

[cli]

; These are paths to (optional) external binaries used in
; certain plug-ins or advanced program features.

; Using full paths to the binaries is recommended.

; tar (used in backup plugin, translation packaging)
tar = /bin/tar

; egrep (used in copyAccessLogFileTool)
egrep = /bin/egrep

; gzip (used in FileManager)
gzip = /bin/gzip

; On systems that do not have PHP4's Sablotron/xsl or PHP5's libxsl/xslt
; libraries installed, or for those who require a specific XSLT processor,
; you may enter the complete path to the XSLT renderer tool, with any
; required arguments. Use %xsl to substitute the location of the XSL
; stylesheet file, and %xml for the location of the XML source file; eg:
; /usr/bin/java -jar ~/java/xalan.jar -IN %xml -XSL %xsl %params
; See xslt_parameter_option below for information on the %params token.
xslt_command = ""

; For providing XSL parameters to the XSL transformer configured in
; xslt_command, the following snippet will be repeated once for each parameter
; to be supplied. %n will be replaced with the parameter name and %v will be
; replaced by the parameter value. The set of options thus constructed will be
; inserted into the xslt_command above in place of the %params token.
xslt_parameter_option = "-PARAM %n %v "


;;;;;;;;;;;;;;;;;;
; Proxy Settings ;
;;;;;;;;;;;;;;;;;;

[proxy]

; The HTTP proxy configuration to use
; http_proxy = "http://username:password@192.168.1.1:8080"
; https_proxy = "https://username:password@192.168.1.1:8080"


;;;;;;;;;;;;;;;;;;
; Debug Settings ;
;;;;;;;;;;;;;;;;;;

[debug]

; Display a stack trace when a fatal error occurs.
; Note that this may expose private information and should be disabled
; for any production system.
show_stacktrace = Off

; Display an error message when something goes wrong.
display_errors = Off

; Display deprecation warnings
deprecation_warnings = Off

; Log web service request information for debugging
log_web_service_info = Off

; declare a cainfo path if a certificate other than PHP's default should be used for curl calls.
; This setting overrides the 'curl.cainfo' parameter of the php.ini configuration file.
[curl]
; cainfo = ""

;;;;;;;;;;;;;;;;;;;;;;;
; Job Queues Settings ;
;;;;;;;;;;;;;;;;;;;;;;;

[queues]

; Default queue driver
default_connection = "database"

; Default queue to use when a job is added to the queue
default_queue = "queue"

; Whether or not to turn on the built-in job runner
;
; When enabled, jobs will be processed at the end of each web
; request to the application.
;
; Use of the built-in job runner is highly discouraged for high-volume 
; sites. Instead, a worker daemon or cron job should be configured
; to process jobs off the application's main thread.
;
; See: <link-to-documentation>
;
job_runner = On

; The maximum number of jobs to run in a single request when using
; the built-in job runner.
job_runner_max_jobs = 30

; The maximum number of seconds the built-in job runner should spend
; running jobs in a single request.
;
; This should be less than the max_execution_time the server has 
; configured for PHP.
;
; Lower this setting if jobs are failing due to timeouts.
job_runner_max_execution_time = 30

; The maximum consumerable memory that should be spent by the built-in
; job runner when running jobs.
;
; Set as a percentage, such as 80%: 
;
; job_runner_max_memory = 80
;
; Or set as a fixed value in megabytes:
; 
; job_runner_max_memory = 128M
;
; When setting a fixed value in megabytes, this should be less than the
; memory_limit the server has configured for PHP.
job_runner_max_memory = 80
