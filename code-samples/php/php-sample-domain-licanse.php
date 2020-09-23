<?php

function domain_licance()
{
	$lic_domain = "aponkral.dev";

	if (!isset($_SERVER["SERVER_NAME"]))
		exit("Domain is not found. The file " . __FILE__ . " is protected by Aponkral.Dev");

	$current_domain = (substr($_SERVER["SERVER_NAME"], 0, 4) === "www.") ? substr($_SERVER["SERVER_NAME"], 4) : $_SERVER["SERVER_NAME"];

	if($current_domain !== $lic_domain) {
		unset($lic_domain, $current_domain);
		exit("Domain is not licensed. The file " . __FILE__ . " is protected by Aponkral.Dev.");
	}
	else
		unset($lic_domain, $current_domain);
}

/**
* Alan adını 5. satırdaki $lic_domain değişkeninden tanımlayabilirsiniz.
* Lisanslanan alan adının www alt alan adı da lisanlanmış olur.
* Sadece bir adet alan adı lisanslanabilir.
* Lisans kontrolü için domain_licanse() fonksiyonu yazılımınızın kodlarından önce çağrılır.
* Fonksiyon geçerli alan adı ile lisanslanan alan adını karşılaştıracaktır.
* Eğer uyuşmazlık varsa fonksiyon hata mesajıyla birlikte çalışmayı durdurur.
* Eğer uyuşmazlık yoksa fonksiyon çalışmayı devam ettirecektir.
*/

domain_licanse();

?>