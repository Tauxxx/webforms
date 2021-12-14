<?
// 
use \Bitrix\Main,
Bitrix\Sale; 
\Bitrix\Main\Loader::includeModule('sale');

$arrOrderedProducts = array(); 
$arrNotOrderedProducts = array(); 
$arrMinProducts = array(); 
$arrMaxProducts = array();
$iblockId = 5; 
$flag = 1; 
$i = 1; 
$j = 0;   

$dbRes = \Bitrix\Sale\Order::getList([
	'select' => ['ID'],
	'filter' => [">=DATE_INSERT" => date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), mktime(0, 0, 0, date("n")-1, 1, date("Y"))),
	"<=DATE_INSERT" => date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), mktime(0, 0, 0, date("n")-1, 31, date("Y")))],
	'order' => ['ID' => 'DESC']
]);

while ($order = $dbRes->fetch()){
	$listBaskets = Sale\Order::load($order)->getBasket();

	foreach ($listBaskets->getBasketItems() as $basket) {
		$productId = $basket->getProductId();
		
		if(!in_array($productId,$arrOrderedProducts)) {
			$arrOrderedProducts[] = $productId;
		}
	}
}
 
$resProducts = CIBlockElement::GetList(
	Array("CATALOG_PRICE_1" => "ASC"),
	Array("IBLOCK_ID" => $iblockId, "!=ID"=>$arrOrderedProducts, 'ACTIVE' => 'Y', "!=CATALOG_PRICE_1" => false),
	false,
	false,
	Array("ID", "NAME", "CATALOG_PRICE_1")
);

while($arProduct = $resProducts->Fetch()) {  
	$arrNotOrderedProducts[$flag]['ID'] = $arProduct['ID'];
	$arrNotOrderedProducts[$flag]['NAME'] = $arProduct['NAME'];
	$arrNotOrderedProducts[$flag]['PRICE'] = $arProduct['CATALOG_PRICE_1'];
	$flag++;
}

$minPrice = $arrNotOrderedProducts[array_key_first($arrNotOrderedProducts)];
$maxPrice = $arrNotOrderedProducts[array_key_last($arrNotOrderedProducts)];

while($arrNotOrderedProducts[$i]['PRICE'] == $minPrice['PRICE']) { 
	if(!empty($arrMinProducts)) {
		if($arrNotOrderedProducts[$i]['ID'] > $arrMinProducts[0]['ID']) {
			$arrMinProducts[0] = $arrNotOrderedProducts[$i];
		} 
	} else { 
		$arrMinProducts[0] = $arrNotOrderedProducts[$i];
	}
	$i++;
}

$j = array_key_last($arrNotOrderedProducts); 

while($arrNotOrderedProducts[$j]['PRICE'] == $maxPrice['PRICE']) { 
	if(!empty($arrMaxProducts)) {
		if($arrNotOrderedProducts[$i]['ID'] > $arrMaxProducts[0]['ID']) {
			$arrMaxProducts[0] = $arrNotOrderedProducts[$j];
		} 
	} else { 
		$arrMaxProducts[0] = $arrNotOrderedProducts[$j];
	} 
	$j--;
}

echo('<b>Товар с наименьшей ценой</b> <hr>'); 
echo('ID - '. $arrMinProducts[0]['ID'].'<br>');
echo('NAME - '. $arrMinProducts[0]['NAME'].'<br>');
echo('PRICE - '. $arrMinProducts[0]['PRICE'].'<br>');  

echo('<br><br>');

echo('<b>Товар с наибольшей ценой</b> <hr>');
echo('ID - '. $arrMaxProducts[0]['ID'].'<br>');
echo('NAME - '. $arrMaxProducts[0]['NAME'].'<br>');
echo('PRICE - '. $arrMaxProducts[0]['PRICE'].'<br>'); 

?>
