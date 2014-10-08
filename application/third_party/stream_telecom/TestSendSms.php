<?php	
	$server = 'http://gateway.api.sc/rest/';
	header ("Content-Type: text/html; charset=utf-8");
	include_once('StreamClass.php');
 	$stream = new STREAM();
	
// данные пользователя
	$login = 'Логин';							//логин 
	$password = 'Пароль';								//пароль 

	$sourceAddress = 'Имя отправителя';						//имя отправителя сообщения (отличное от testsms, имя отправителя Вы
														//можете запросить в личном кабинете)
	$destinationAddress = 'Номер абонента';				//номер получателя единичного сообщения (в формате 79111234567 для РФ)
	$destinationAddresses = 'Номер абонента1, Номер абонента2'; 	//номера для массовой отправки сообщений (указываются через запятую)
	$data = 'Проверка';									//текст сообщения
	$sendDate = '2013-09-12T09:00:00';					//время отправки сообщения (необязательный параметр для единичной отправки)
	$validity = 1440;									//время жизни сообщения, в минутах (необязательный параметр)

// данные для запроса статистики
	$startDateTime = '2013-09-12T00:00:00';				//начало периода для запроса статистики сообщений
	$endDateTime = '2013-09-13T23:59:00';				//конец периода для запроса статистики сообщений
// данные для запроса входящих сообщений
	$minDateUTC = '2013-01-01T00:00:00';				//начало периода для запроса входящих сообщений (указывается по UTC)
	$maxDateUTC = '2013-01-03T00:00:00';				//конец периода для запроса входящих сообщений (указывается по UTC)

 	$sms = array(	
			array("phone" => "Номер абонента1","text" => "Текст1"),	//массив номеров и текста сообщений для пакетной отправки
			array("phone" => "Номер абонента2","text" => "Текст2")		//phone - номер телефона, text - текст сообщения
				  ); 
	
// запрос на получение идентификатора сессии
 	echo "<h2>SessionID:</h2>";
	echo '<table><tr><td width = "300">Передача параметров пользователя: </td><td><b>$stream->GetSessionID("'.$server.'","'.$login.'", "*пароль*"); </b></td></tr> <tr><td>Полученный результат (session ID): </td>';
	$session = $stream -> GetSessionId($server,$login,$password);
	echo '<td><b>';
	print_r($session);
	echo '</b></td></tr></table>';	
	echo '<hr>';
/*
// запрос баланса пользователя
 	echo "<h2>Balance:</h2>";
	echo '<table><tr><td width = "300">Передача параметров пользователя: </td><td><b>$stream->GetBalance("'.$server.'","'.$session.'"); </b></td></tr> <tr><td>Полученный результат (balance): </td>';
	$balance = $stream -> GetBalance($server,$session);
	echo '<td><b>';
	print_r($balance);
	echo '</b></td></tr></table>';	
	echo '<hr>';
 
// отправка единичного sms-сообщения
	echo "<h2>Send SMS:</h2>";
	echo '<table><tr><td width = "300">Единичная отправка sms-сообщения: </td><td><b>$stream->SendSms("'.$server.'","'.$session.'","'.$sourceAddress.'", "'.$destinationAddress.'", "'.$data.'","'.$validity.'","'.$sendDate.'"); </b></td></tr> <tr><td>ID отправленного сообщения: </td><td><b>';
	$send_sms = $stream -> SendSms($server,$session,$sourceAddress,$destinationAddress,$data,$validity,$sendDate);	
	print_r($send_sms);
	echo '</b></td></tr></table>';		
	echo '<hr>';

 // отправка sms-сообщения нескольким получателям
	echo "<h2>Send Bulk:</h2>";
	echo '<table><tr><td width = "300">Отправка sms нескольким адресатам: </td><td><b>$stream->sendBulk("'.$server.'","'.$session.'","'.$sourceAddress.'","'.$destinationAddresses.'", "'.$data.'","'.$validity.'"); </b></td></tr> <tr><td>ID отправленных сообщений: </td><td><b>';
	$send_bulk = $stream -> SendBulk($server,$session,$sourceAddress,$destinationAddresses,$data,$validity);	
	print_r($send_bulk);
	echo '</b></td></tr></table>';	
	echo '<hr>';

// отправка SMS-сообщения с учетом часового пояса получателя
	echo "<h2>Send SMS By Time Zone:</h2>";
	echo '<table><tr><td width = "300">Отправка с учетом часового пояса: </td><td><b>$stream->SendByTime("'.$server.'","'.$session.'","'.$sourceAddress.'","'.$destinationAddress.'", "'.$data.'","'.$validity.'","'.$sendDate.'"); </b></td></tr> <tr><td>ID отправленного сообщения: </td><td><b>';
	$send_by_time = $stream -> SendByTime($server,$session,$sourceAddress,$destinationAddress,$data,$validity,$sendDate);	
	print_r($send_by_time);
	echo '</b></td></tr></table>';		
	echo '<hr>';

//пакетная отправка сообщений (отправка сообщений с разным текстом на разные номера)
	echo "<h2>Send Bulk Packet:</h2>";
	echo '<table><tr><td width = "300">Пакетная отправка сообщений: </td><td><b>$stream->SendByTime("'.$server.'","'.$session.'","'.$sourceAddress.'", $stream -> JsonArray("';
	print_r($sms);
	echo '"), "'.$validity.'"); </b></td></tr> <tr><td>ID отправленного сообщения: </td><td><b>';
	$send_bulk_packet = $stream -> SendBulkPacket($server,$session,$sourceAddress,$stream -> JsonArray($sms),$validity);		
	print_r($send_bulk_packet);
	echo '</b></td></tr></table>';	
	echo '<hr>';	
 	 
// запрос на получение статистики по сообщениям, за выбранный период времени
 	echo "<h2>Get Statistic:</h2>";
	echo '<table><tr><td width = "300">Запрос статистики sms-сообщений</td><td><b>$stream->GetStatistic("'.$server.'","'.$session.'","'.$startDateTime.'","'.$endDateTime.'")</b></td></tr><tr><td>Количество отправленных сообщений</td><td><b>Sent</b></td></tr><tr><td>Количество доставленных сообщений</td><td><b>Delivered</b></td></tr><tr><td>Количество недоставленных сообщений</td><td><b>NotDelivered</b></td></tr><tr><td>Сообщений в процессе отправки</td><td><b>InProcess</b></td></tr><tr><td>Количество просроченых сообщений</td><td><b>Expired</b></td></tr><tr><td>Процент доставленных сообщений</td><td><b>DeliveryRatio</b></td></tr><tr><td>Полученный результат</td><td><b>';
	$statistic = $stream -> GetStatistic($server,$session,$startDateTime,$endDateTime);
	print_r($statistic);
	echo '</b></td></tr></table>';	
	echo '<hr>';

// запрос на получение входящих сообщений (данная услуга подключается у менеджера)
	echo "<h2>GetMessageIn:</h2>";
	$incoming = $stream -> GetIncomingSms($server,$session,$minDateUTC,$maxDateUTC);	
	echo '<table><tr><td width = "300">Запрос входящих sms-сообщений</td><td><b>$stream->GetIncomingSms("'.$server.'","'.$session.'","'.$minDateUTC.'","'.$maxDateUTC.'")</b></td></tr><tr><td>Префикс</td><td><b>Prefix</b></td></tr><tr><td>Текст принятого сообщения</td><td><b>Data</b></td></tr><tr><td>Имя отправителя</td><td><b>SourceAdress</b></td></tr><tr><td>Номер получателя сообщения</td><td><b>DestanationAdress</b></td></tr><tr><td>Дата отправки сообщения (по UTC)</td><td><b>CreatedDateUTC</b></td></tr><tr><td>Полученный результат</td><td><b>';
	print_r($incoming);
	echo '</b></td></tr></table>';		
	echo '<hr>';	
	
// запрос на получение статуса сообщения ($send_sms[0] - статус полученный, при отправке единичного sms-сообщения)
	echo "<h2>GetMessageState:</h2>";
	echo '<table><tr><td width = "300">Запрос статуса sms-сообщения</td><td><b>$stream->getSMSState("'.$server.'","'.$session.'","'.$send_sms[0].'")</b></td></tr><tr><td>Статус отправленного sms-сообщения</td><td><b>State</b></td></tr><tr><td>Дата доставки sms-сообщения</td><td><b>ReportedDateUtc</b></td></tr><tr><td>Расшифровка статуса </td><td><b>StateDescription</b></td></tr><tr><td>Стоимость отправленного сообщения</td><td><b>Price</b></td></tr><tr><td>Полученный результат:</td><td><b>';
	$state = $stream -> GetState($server,$session,$send_sms[0]);
	print_r($state);
	echo '</b></td></tr></table>';		
	echo '<hr>';
*/
?>