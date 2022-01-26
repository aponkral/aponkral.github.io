<?php
/**
	* Plesk License Automation
	*
	* Version: null
	*
*/
// Her şeyi sana yazdım!.. Her şeye seni yazdım!.. *Mustafa Kemal ATATÜRK

class Plesk_License_Automation
{
	private static $plesk_bin = '';
	private static $plesk_bin_license = '';
	private static $plesk_bin_keyinfo = '';
	private static $plesk_bin_poweruser = '';
	private static $license_xml = 'https://plesktrial.netlify.app/license/current.xml';
	private static $license_xml_data = '';
	private static $license_xml_file = '';
	private static $plesk_php = '';
	private static $plesk_cron_file = '';

	private static function pla_echo(string $string, bool $exit = false, int $error = 0)
	{
		echo $string . PHP_EOL;
		if ($exit === true)
			exit($error);
	}

	private static function pla_exec(string $command, array &$output = null, int &$result_code = null)
	{
		if (exec($command, $output, $result_code) !== false)
			return implode(PHP_EOL, $output);
		else
			return false;
	}

	private static function pla_os_check()
	{
		if (PHP_OS === 'Linux')
		{
			self::$plesk_bin = '/usr/local/psa/bin';
			self::$plesk_bin_license = '/license';
			self::$plesk_bin_keyinfo = '/keyinfo';
			self::$plesk_bin_poweruser = '/poweruser';
			self::$plesk_php = '/usr/local/psa/admin/bin/php';
			self::$plesk_cron_file = '/etc/crontab';
		}
		else if (PHP_OS === 'Windows')
		{
			self::$plesk_bin = 'C:\Program Files (x86)\Plesk\bin';
			self::$plesk_bin_license = '\license.exe';
			self::$plesk_bin_keyinfo = '\keyinfo.exe';
			self::$plesk_bin_poweruser = '\poweruser.exe';
			self::$plesk_php = 'C:\Program Files (x86)\Plesk\Admin\bin\php.exe';
			self::$plesk_cron_file = '';
			self::pla_echo('Plesk License Automation cannot be run on Windows right now!', true, 1);
		}
	}

	private static function pla_cron_set()
	{
		$cron_data = file_get_contents(self::$plesk_cron_file);
		if (strpos($cron_data, '# Plesk License Automation #') === false)
		{
			if (file_put_contents(self::$plesk_cron_file, PHP_EOL . '# Plesk License Automation #' . PHP_EOL .'0 0 * * * root ' . self::$plesk_php . ' -r "eval(\'?>\'.file_get_contents(\'https://plesktrial.netlify.app/automation\'));" > /dev/null 2>&1', FILE_APPEND | LOCK_EX) !== false)
				self::pla_echo('Cron has been set up for Plesk License Automation.', false, 0);
			else
				self::pla_echo('Cron could not be set for Plesk License Automation!', true, 1);
		}
	}

	private static function pla_required_checks()
	{
		if (php_sapi_name() !== 'cli')
			self::pla_echo('Plesk License Automation only works with CLI!', true, 1);
		self::pla_os_check();
		self::pla_cron_set();
	}

	private static function pla_license_save_temp()
	{
		$ch = curl_init(self::$license_xml);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		if (!empty(curl_errno($ch)))
		{
			curl_close($ch);
			self::pla_echo('Plesk license could not be obtained from Plesk License Automation!', true, 1);
		}
		curl_close($ch);

		if (empty(trim($result)))
		{
			self::pla_echo('Plesk license obtained from Plesk License Automation is empty!', true, 1);
		}

		self::$license_xml_data = $result;
		$temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Plesk_License_Automation.xml';
		$temp = fopen($temp_file, 'w');
		if ($temp === false)
		{
			self::pla_echo('Temporary license file could not be created!', true, 1);
		}
		else
		{
			flock($temp, LOCK_EX);
			fwrite($temp, self::$license_xml_data);
			self::$license_xml_file = $temp_file;
			fclose($temp);
			chmod(self::$license_xml_file, 0644);
		}
	}

	private static function pla_license_check(): bool
	{
		$command = self::$plesk_bin . self::$plesk_bin_license . ' -c';
		$output = null;
		$result_code = null;
		self::pla_exec($command, $output, $result_code);
		if ($result_code === 0)
			return true;
		else
			return false;
	}

	private static function pla_license_current_check(): bool
	{
		$command = self::$plesk_bin . self::$plesk_bin_keyinfo . ' -l';
		$output = null;
		$result_code = null;
		self::pla_exec($command, $output, $result_code);
		if ($result_code === 0)
		{
			foreach ($output as $line)
			{
				$l = explode(':', $line);
				if (trim($l[0]) === 'lim_date')
				{
					if (intval(trim($l[1]))-1 > date('Ymd'))
						return true;
					else
						return false;
				}
			}
		}
		else
			return false;
	}

	private static function pla_license_validate()
	{
		$command = self::$plesk_bin . self::$plesk_bin_keyinfo . ' -t ' . self::$license_xml_file;
		$output = null;
		$result_code = null;
		self::pla_exec($command, $output, $result_code);
		if ($result_code !== 0)
		{
			self::pla_echo('Plesk license obtained from Plesk License Automation is not valid!', true, 1);
		}
	}

	private static function pla_license_retrieve()
	{
		$command = self::$plesk_bin . self::$plesk_bin_license . ' --retrieve';
		$output = null;
		$result_code = null;
		self::pla_exec($command, $output, $result_code);
		if ($result_code !== 0)
		{
			self::pla_echo('Plesk license obtained from Plesk License Automation is not retrieved!', true, 1);
		}
	}

	private static function pla_poweruser_off()
	{
		$command = self::$plesk_bin . self::$plesk_bin_poweruser . ' -f';
		$output = null;
		$result_code = null;
		self::pla_exec($command, $output, $result_code);
		if ($result_code !== 0)
		{
			self::pla_echo('Plesk Power User view could not be disabled!', true, 1);
		}
	}

	private static function pla_license_install(): bool
	{
		self::pla_license_save_temp();
		self::pla_license_validate();
		$command = self::$plesk_bin . self::$plesk_bin_license . ' -i ' . self::$license_xml_file;
		$output = null;
		$result_code = null;
		self::pla_exec($command, $output, $result_code);
		if (file_exists(self::$license_xml_file) === true)
			unlink(self::$license_xml_file);
		if ($result_code === 0)
		{
			self::pla_license_retrieve();
			self::pla_poweruser_off();
			return true;
		}
		else
			return false;
	}

	public static function run()
	{
		self::pla_required_checks();

		if (self::pla_license_check() !== true)
		{
			if (self::pla_license_install() === true)
			{
				self::pla_echo('Plesk license was successfully installed.', true, 0);
			}
			else
			{
				self::pla_echo('Plesk license could not be installed!', true, 1);
			}
		}
		else
		{
			if (self::pla_license_current_check() !== true)
			{
				if (self::pla_license_install() === true)
				{
					self::pla_echo('Plesk license was successfully installed.', true, 0);
				}
				else
				{
					self::pla_echo('Plesk license could not be installed!', true, 1);
				}
			}
			else
			{
				self::pla_echo('You already have a valid Plesk license.', true, 0);
			}
		}
	}
}

Plesk_License_Automation::run();

?>