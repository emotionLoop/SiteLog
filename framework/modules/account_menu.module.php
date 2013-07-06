<?php
/**
 * Account menu is the module that controls the top right account menu.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Module
 * @category Account Menu
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class Account_menu extends Module {
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme
		
		$nonce = SS::get('_wznonce','post','','string');
		
		//-- Account-related error messages
		$error = SS::get('msg','get','','string');
		if (!empty($error)) {
			switch ($error) {
				case 'error':
					FW::error(__("Well, this is awkward... There was an error, but we're not sure what happened. Try again and if it keeps happening, let us know."));
				break;
				case 'login':
					FW::error(__('Invalid login details. Please try again or contact us.'));
				break;
				case 'required':
					FW::error(__('Please fill in all required fields correctly.'));
				break;
				case 'private':
					FW::error(__("The page you're trying to access requires you to login!"));
				break;
				case 'time':
					FW::error(__('There was a problem verifying your request. You seem to have waited too long. Please try again.'));
				break;
				case 'logout':
					FW::msg(__('Logout successful.'));
				break;
				case 'account':
					FW::msg(__("Account deleted successfully. You'll receive a confirmation email and that's it. Sorry to see you go."));
				break;
				case 're-login':
					FW::msg(__('You successfully changed your email and/or password! You need to login with your new details!'));
				break;
				case 'logout':
					FW::msg(__('You logged out successfully.'));
				break;
				case 'recover':
					FW::msg(__('A New Password was sent to your email.'));
				break;
				case 'duplicate':
					FW::error(__("The email you're trying to use already exists in the database."));
				break;
				case 'signup':
					FW::msg(__("Bam! You're signed up. You should be getting an email any minute with your password and information. Tell us if you don't get it in the next 15 minutes."));
				break;
			}
		}
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
		if (Usr::logged()) {
			$attrs = null;
			if (in_array('account', ST::$segs)) {
				$attrs = array('class' => 'active');
			}
?>
	<?php echo FW::url('account/', __('My Account'), $attrs); ?><?php echo FW::url('account/?doLogout=1', __('Logout')); ?>
<?php
		} else {
?>
	<?php echo FW::url('#!/login', __('Login'), array('id' => "login-button")); ?>
<?php
		}
	}
}
?>