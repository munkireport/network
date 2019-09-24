<?php 

$this->view('listings/default',
[
	"i18n_title" => 'network.report',
	"table" => [
		[
			"column" => "machine.computer_name",
			"i18n_header" => "listing.computername",
			"formatter" => "clientDetail",
			"tab_link" => "network-tab",
		],
		[
			"column" => "reportdata.serial_number",
			"i18n_header" => "displays_info.machineserial",
		],
		[
			"column" => "reportdata.long_username",
			"i18n_header" => "username",
		],  
		["column" => "network.service", "i18n_header" => "network.service",],
		[
			"column" => "network.status",
			"i18n_header" => "status",
			"formatter" => "binaryEnabledDisabled",
		],
		["column" => "network.ethernet", "i18n_header" => "network.ethernet",],
		["column" => "network.ipv4ip", "i18n_header" => "network.ip_address",],
		["column" => "network.ipv4dns", "i18n_header" => "network.dns",],
		["column" => "network.ipv4router", "i18n_header" => "network.router",],
		["column" => "network.externalip", "i18n_header" => "network.externalip",],
		["column" => "network.ipv4mask", "i18n_header" => "network.mask",],
		["column" => "network.activemedia", "i18n_header" => "network.activemedia",],
	]
]);