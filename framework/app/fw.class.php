<?php
/**
 * FW is the customized Framework class, to be changed per project.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage FW
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class FW extends Framework {
	/**
	 * Public mail() method. Used to send an HTML email.
	 *
	 * @uses System::startCache()
	 * @uses System::endCache()
	 * @uses Settings::get()
	 *
	 * @param $name String From name.
	 * @param $email String From email.
	 * @param $to_mail String To email.
	 * @param $subject String Email's subject.
	 * @param $msg String HTML Email message.
	 * @param $attachment String absolute path to a file. Defaults to null.
	 *
	 * @return true/faste Boolean true if mail was sent, false otherwise.
	 */
	public static function mail($name, $email, $to_mail, $subject, $msg, $attachment = null) {
		$sending = false;

		if (!empty($name) && !empty($email) && !empty($to_mail) && !empty($subject) && !empty($msg)) {
			$from_name = $name;
			$from_mail = $email;
			$sending = true;
		}

		if ($sending) {
			$eol = "\n";

			Sys::startCache();
?>
#outlook a{padding:0;}
body{width:100% !important;}
.ReadMsgBody{width:100%;} .ExternalClass{width:100%;}
body{-webkit-text-size-adjust:none;}			

/* Reset Styles */
body{margin:0; padding:0;}
img{border:0; height:auto; line-height:100%; outline:none; text-decoration:none;}
table td{border-collapse:collapse;}
#backgroundTable{height:100% !important; margin:0; padding:0; width:100% !important;}

/* Template Styles */

body, #backgroundTable {
	background-color: #FFF;
}
#templateContainer {
	border: 1px solid #CCC;
}
h1 {
	color: #BF3360;
	display: block;
	font-family: arial;
	font-size: 28px;
	font-weight: bold;
	line-height: 125%;
	margin-top: 0;
	margin-right: 0;
	margin-bottom: 10px;
	margin-left: 0;
	text-align: left;
}
h2 {
	color: #333;
	display: block;
	font-family: arial;
	font-size: 24px;
	font-weight: bold;
	line-height: 125%;
	margin-top: 0;
	margin-right: 0;
	margin-bottom: 10px;
	margin-left: 0;
	text-align: left;
}
h3 {
	color: #090;
	display: block;
	font-family: arial;
	font-size: 20px;
	font-weight: bold;
	line-height: 125%;
	margin-top: 0;
	margin-right: 0;
	margin-bottom: 10px;
	margin-left: 0;
	text-align: left;
}
h4 {
	color: #333;
	display: block;
	font-family: arial;
	font-size: 18px;
	font-weight: bold;
	line-height: 125%;
	margin-top: 0;
	margin-right: 0;
	margin-bottom: 10px;
	margin-left: 0;
	text-align: left;
}
p {
	font-family: arial;
	font-size: 14px;
	line-height: 130%;
}
#browser-link {
	background-color: #FFF;
}
#browser-link div {
	color: #666;
	font-family: arial;
	font-size: 10px;
	line-height: 100%;
	text-align: center;
}
#browser-link div a {
	color: #090;
	font-weight: normal;
	text-decoration: none;
}
#header {
	background-color: #009b00;
	color: #202020;
	font-family: arial;
	font-size: 16px;
	font-weight: 400;
	line-height: 100%;
	padding: 0;
	text-align: center;
	vertical-align: middle;
}
#header a {
	color: #fff;
	font-weight: 400;
	text-decoration: none;
	text-shadow: 0 1px 1px rgba(0,0,0,.4);
}
#templateContainer, .bodyContent {
	 background-color: #FFFFFF;
}
.bodyContent div {
	color: #666;
	font-family: arial;
	font-size: 14px;
	line-height: 150%;
	text-align: left;
}
.bodyContent div a {
	color: #090;
	font-weight: normal;
	text-decoration: none;
}	
.bodyContent img {
	display: inline;
	height: auto;
	margin-bottom: 10px;
}
#footer {
	background-color: #ECECEC;
	border-top: 1px solid #CCC;
	color: #666;
	font-family: arial;
	font-size: 12px;
	line-height: 125%;
	padding-left: 5px;
	padding-right: 5px;
}
#footer img {
	display: inline;
}
#social span {
	margin-right: 6px;
}
#social span img {
	vertical-align: middle;
}
#copyright {
	font-size: 11px;
	color: #666;
	text-align: right;
}
#copyright a {
	font-weight:bold;
	color: #666;
	text-decoration: none;
}
<?php
			$theCSS = Sys::endCache();

			include_once('framework/classes/emogrifier.class.php');
			$emogrifier = new Emogrifier();

			include_once('framework/classes/mandrill.class.php');
			Mandrill::setApiKey('MANDRILL-API-KEY');// Mandrill API Key

			$tosend['email'] = $to_mail;
			$tosend['subject'] = $subject;

			$tosend['message'] = '';
			Sys::startCache();
?>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
	<center>
		<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable">
			<tr>
				<td align="center" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer">
						<tr>
							<td align="center" valign="top">
								<!-- Header -->
								<table border="0" cellpadding="2" cellspacing="0" width="600" id="header" background="<?php echo ST::$url; ?>images/email/header-bg.png">
									<tr>
					<td><?php echo FW::url('',FW::img(ST::$url.'images/email/sitelog-logo.png',ST::get('default_title'),array('width' => '85', 'height' => '26'))); ?></td>
					<td style="padding-left:60px;"><?php echo FW::url('tour/',__('Tour')); ?></td>
					<td><?php echo FW::url('plans-and-pricing/',Sys::esc_html(__('Plans & Pricing'))); ?></td>
					<td><?php echo FW::url('http://support.emotionloop.com/kb/sitelog',__('Support')); ?></td>
					<td style="padding-left: 30px; padding-right:20px;"><?php echo FW::url(ST::$url.'#!/login',__('Login')); ?></td>
									</tr>
								</table>
								<!-- / Header -->
							</td>
						</tr>
						<tr>
							<td align="center" valign="top">
								<!-- Body -->
								<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody">
									<tr>
										<td valign="top" class="bodyContent">
					<table border="0" cellpadding="20" cellspacing="0" width="100%">
												<tr>
													<td valign="top">
														<div>
															<?php echo $msg; ?>
														</div>
													</td>
												</tr>
											</table>												
										</td>
									</tr>
								</table>
								<!-- / Body -->
							</td>
						</tr>
						<tr>
							<td align="center" valign="top">
								<!-- Footer -->
								<table border="0" cellpadding="0" cellspacing="0" width="600" id="footer">
									<tr>
										<td valign="top">
					<table border="0" cellpadding="5" cellspacing="0" width="100%">
												<tr>
													<td colspan="2" valign="middle" id="social">
							<span><?php echo FW::url('http://twitter.com/SiteLogHQ',FW::img(ST::$url.'images/social/twitter.png',__('Follow us on Twitter'),array('width' => '32', 'height' => '32'))); ?></span> 
							<span><?php echo FW::url('http://www.facebook.com/SiteLog',FW::img(ST::$url.'images/social/facebook.png',__('Like us on Facebook'),array('width' => '32', 'height' => '32'))); ?></span> 
													</td>
						<td>
							<div id="copyright">
							<?php echo FW::url('',__('SiteLog')); ?>
							<span><?php echo ST::get('sitelog_version'); ?></span> // &copy; <?php _e('by'); ?> <?php echo FW::url('http://emotionloop.com',__('emotionLoop')); ?>
							</div>
						</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
								<!-- / Footer -->
							</td>
						</tr>
					</table>
					<br />
				</td>
			</tr>
		</table>
	</center>
</body>

<?php
			$theHTML = Sys::endCache();

			$emogrifier->setHTML($theHTML);
			$emogrifier->setCSS($theCSS);

			$tosend['message'] = $emogrifier->emogrify();
			$tosend['headers'] = "From: \"".$from_name."\" <".$from_mail.">".$eol;
			$tosend['headers'] .= "Return-path: <".$from_mail.">".$eol;
			$tosend['headers'] .= "MIME-Version: 1.0".$eol;
			if (!empty($attachment)) {
				$file = $attachment;
				$content = file_get_contents($file);
				$content = chunk_split(base64_encode($content));
				$uid = md5(uniqid(time()));
				$f_name = str_replace(ST::get('path'),'',$attachment);
				$tosend['headers'] .= "Content-Type: multipart/mixed; boundary=\"PHP-mixed-".$uid."\"".$eol.$eol;
				$tosend['headers'] .= "This is a multi-part message in MIME format.".$eol;
				$tosend['headers'] .= "--PHP-mixed-".$uid."".$eol;
				$tosend['headers'] .= "Content-Type: multipart/alternative; boundary=\"PHP-alt-".$uid."\"".$eol.$eol;
				$tosend['headers'] .= "--PHP-alt-".$uid."".$eol;
				$tosend['headers'] .= "Content-type: text/html; charset=utf-8".$eol;
				$tosend['headers'] .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
				$tosend['headers'] .= $tosend['message']."".$eol.$eol;
				$tosend['headers'] .= "--PHP-alt-".$uid."--".$eol;
				$tosend['headers'] .= "--PHP-mixed-".$uid."".$eol;
				$tosend['headers'] .= "Content-Type: application/octet-stream; name=\"".$f_name."\"".$eol;
				$tosend['headers'] .= "Content-Transfer-Encoding: base64".$eol;
				$tosend['headers'] .= "Content-Disposition: attachment; filename=\"".$f_name."\"".$eol.$eol;
				$tosend['headers'] .= $content."".$eol.$eol;
				$tosend['headers'] .= "--PHP-mixed-".$uid."--";
				$tosend['message'] = "";//-- The message is already in the headers.
			} else {
				$tosend['headers'] .= "Content-type: text/html; charset=utf-8".$eol;
			}

			/*if (mail($tosend['email'], $tosend['subject'],  $tosend['message'] , $tosend['headers'])) {
				return true;
			} else {
				return false;
			}*/

			$JSONMandrillVariable = new stdClass();
			$JSONMandrillVariable->type = 'messages';
			$JSONMandrillVariable->call = 'send';
			$JSONMandrillVariable->message = new stdClass();
			$JSONMandrillVariable->message->html = $tosend['message'];
			$JSONMandrillVariable->message->subject = $tosend['subject'];
			$JSONMandrillVariable->message->from_email = $from_mail;
			$JSONMandrillVariable->message->from_name = $from_name;
			$JSONMandrillVariable->message->to = array();
			$JSONMandrillVariable->message->to[0] = new stdClass();
			$JSONMandrillVariable->message->to[0]->email = $to_mail;
			$JSONMandrillVariable->message->to[0]->name = '';//-- TODO: Get name from email automagically
			$JSONMandrillVariable->message->track_opens = true;
			$JSONMandrillVariable->message->track_clicks = false;
			$JSONMandrillVariable->message->auto_text = true;
			$JSONMandrillVariable->message->tags = array('default');

			$mandrillResponse = Mandrill::call((array) $JSONMandrillVariable);

			if (isset($mandrillResponse[0]) && isset($mandrillResponse[0]->status) && $mandrillResponse[0]->status == 'sent') {
				return true;
			} else {
				return false;
			}
		}//-- if ($sending)
		return false;
	}

	//-- Get mmi in minutes or hours, whatever fits better.
	public static function humanMMI($mmi) {
		if ($mmi <= 90) {
			$return = $mmi. ' ' . __('min.');
		} else {
			$return = round($mmi / 60).' '. __('hours');
		}

		return $return;
	}

	//-- Get Plans
	public static function getPlans() {
		$sql = "SELECT * FROM `tbl_plans` WHERE `status` = 1 and `public` = 1 and `position` > 0 ORDER BY `position` ASC";
		$res = DB::execute($sql);
		return $res;
	}

	//-- Get Plan
	public static function getPlan($id) {
		$sql = "SELECT * FROM `tbl_plans` WHERE `id` = '".(int)$id."' AND `status` = 1";
		$res = DB::sexecute($sql);
		return $res;
	}

	//-- Get Plan Info
	public static function getPlanInfo($id, $field) {
		$sql = "SELECT `".$field."` FROM `tbl_plans` WHERE `id` = ".(int)$id."";
		$res = DB::get($sql);
		return $res;
	}

	//-- Get User's Plan
	public static function getUserPlan() {
		$id = Usr::get('plan');
		$user = Usr::get('id');

		$sql = "SELECT a.*, b.name, b.paypal FROM `tbl_user_plans` a INNER JOIN `tbl_plans` b ON (a.`plan` = b.`id`) WHERE a.`plan` = '".(int)$id."' AND a.`user` = '".(int)$user."'";
		$res = DB::sexecute($sql);
		return $res;
	}

	public static function getCountryArray() {
		return array(
			'Afghanistan',
			'Albania',
			'Algeria',
			'American Samoa',
			'Andorra',
			'Angola',
			'Anguilla',
			'Antarctica',
			'Antigua and Barbuda',
			'Argentina',
			'Armenia',
			'Aruba',
			'Australia',
			'Austria',
			'Azerbaijan',
			'Bahamas',
			'Bahrain',
			'Bangladesh',
			'Barbados',
			'Belarus',
			'Belgium',
			'Belize',
			'Benin',
			'Bermuda',
			'Bhutan',
			'Bolivia',
			'Bosnia and Herzegovina',
			'Botswana',
			'Bouvet Island',
			'Brazil',
			'British Indian Ocean Territory',
			'Brunei Darussalam',
			'Bulgaria',
			'Burkina Faso',
			'Burundi',
			'Cambodia',
			'Cameroon',
			'Canada',
			'Cape Verde',
			'Cayman Islands',
			'Central African Republic',
			'Chad',
			'Chile',
			'China',
			'Christmas Island',
			'Cocos (Keeling) Islands',
			'Colombia',
			'Comoros',
			'Congo',
			'Congo, the Democratic Republic of the',
			'Cook Islands',
			'Costa Rica',
			'Cote D\'Ivoire',
			'Croatia',
			'Cuba',
			'Cyprus',
			'Czech Republic',
			'Denmark',
			'Djibouti',
			'Dominica',
			'Dominican Republic',
			'Ecuador',
			'Egypt',
			'El Salvador',
			'Equatorial Guinea',
			'Eritrea',
			'Estonia',
			'Ethiopia',
			'Falkland Islands (Malvinas)',
			'Faroe Islands',
			'Fiji',
			'Finland',
			'France',
			'French Guiana',
			'French Polynesia',
			'French Southern Territories',
			'Gabon',
			'Gambia',
			'Georgia',
			'Germany',
			'Ghana',
			'Gibraltar',
			'Greece',
			'Greenland',
			'Grenada',
			'Guadeloupe',
			'Guam',
			'Guatemala',
			'Guinea',
			'Guinea-Bissau',
			'Guyana',
			'Haiti',
			'Heard Island and Mcdonald Islands',
			'Holy See (Vatican City State)',
			'Honduras',
			'Hong Kong',
			'Hungary',
			'Iceland',
			'India',
			'Indonesia',
			'Iran, Islamic Republic of',
			'Iraq',
			'Ireland',
			'Israel',
			'Italy',
			'Jamaica',
			'Japan',
			'Jordan',
			'Kazakhstan',
			'Kenya',
			'Kiribati',
			'Korea, Democratic People\'s Republic of',
			'Korea, Republic of',
			'Kuwait',
			'Kyrgyzstan',
			'Lao People\'s Democratic Republic',
			'Latvia',
			'Lebanon',
			'Lesotho',
			'Liberia',
			'Libyan Arab Jamahiriya',
			'Liechtenstein',
			'Lithuania',
			'Luxembourg',
			'Macao',
			'Macedonia, the Former Yugoslav Republic of',
			'Madagascar',
			'Malawi',
			'Malaysia',
			'Maldives',
			'Mali',
			'Malta',
			'Marshall Islands',
			'Martinique',
			'Mauritania',
			'Mauritius',
			'Mayotte',
			'Mexico',
			'Micronesia, Federated States of',
			'Moldova, Republic of',
			'Monaco',
			'Mongolia',
			'Montserrat',
			'Morocco',
			'Mozambique',
			'Myanmar',
			'Namibia',
			'Nauru',
			'Nepal',
			'Netherlands',
			'Netherlands Antilles',
			'New Caledonia',
			'New Zealand',
			'Nicaragua',
			'Niger',
			'Nigeria',
			'Niue',
			'Norfolk Island',
			'Northern Mariana Islands',
			'Norway',
			'Oman',
			'Pakistan',
			'Palau',
			'Palestinian Territory, Occupied',
			'Panama',
			'Papua New Guinea',
			'Paraguay',
			'Peru',
			'Philippines',
			'Pitcairn',
			'Poland',
			'Portugal',
			'Puerto Rico',
			'Qatar',
			'Reunion',
			'Romania',
			'Russian Federation',
			'Rwanda',
			'Saint Helena',
			'Saint Kitts and Nevis',
			'Saint Lucia',
			'Saint Pierre and Miquelon',
			'Saint Vincent and the Grenadines',
			'Samoa',
			'San Marino',
			'Sao Tome and Principe',
			'Saudi Arabia',
			'Senegal',
			'Serbia and Montenegro',
			'Seychelles',
			'Sierra Leone',
			'Singapore',
			'Slovakia',
			'Slovenia',
			'Solomon Islands',
			'Somalia',
			'South Africa',
			'South Georgia and the South Sandwich Islands',
			'Spain',
			'Sri Lanka',
			'Sudan',
			'Suriname',
			'Svalbard and Jan Mayen',
			'Swaziland',
			'Sweden',
			'Switzerland',
			'Syrian Arab Republic',
			'Taiwan, Province of China',
			'Tajikistan',
			'Tanzania, United Republic of',
			'Thailand',
			'Timor-Leste',
			'Togo',
			'Tokelau',
			'Tonga',
			'Trinidad and Tobago',
			'Tunisia',
			'Turkey',
			'Turkmenistan',
			'Turks and Caicos Islands',
			'Tuvalu',
			'Uganda',
			'Ukraine',
			'United Arab Emirates',
			'United Kingdom',
			'United States',
			'United States Minor Outlying Islands',
			'Uruguay',
			'Uzbekistan',
			'Vanuatu',
			'Venezuela',
			'Viet Nam',
			'Virgin Islands, British',
			'Virgin Islands, U.s.',
			'Wallis and Futuna',
			'Western Sahara',
			'Yemen',
			'Zambia',
			'Zimbabwe'
		);
	}

	public static function getLanguageArray() {
		return array(
			'en' => 'English'
		);
	}

	public static function getMonthArray() {
		return array(
			__('Month'),
			__('January'),
			__('February'),
			__('March'),
			__('April'),
			__('May'),
			__('June'),
			__('July'),
			__('August'),
			__('September'),
			__('October'),
			__('November'),
			__('Decemter')
		);
	}

	public static function getYearArray($start = 0, $end = 0) {
		$return = array();

		if (empty($start)) {
			$start = (int)date('Y');
		}

		if (empty($end)) {
			$end = (int)date('Y') + 10;
		}

		for ($i=$start;$i<$end;$i++) { 
			$return[] = $i;
		}

		return $return;
	}
}
?>