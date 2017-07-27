<?php 
	namespace PHPMail;
	
class PHPMail{
	
	private function send($email, $subject, $msg){
		$mail = new PHPMailer;
		$mail->SMTPOptions =[
			'ssl' => [
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true
			]
		];
		$mail->isSMTP('smtp.gmail.com');                                   
		$mail->Host = 'smtp.gmail.com';  
		$mail->SMTPAuth = true;                             
		$mail->Username = 'xxx';//your gmail username        
		$mail->Password = 'xxx';//your gmail pass                      
		$mail->SMTPSecure = 'tls';                          
		$mail->Port = 587;                                  

		$mail->From = 'xxx';//from
		$mail->FromName = 'Your Company Name';
		$mail->addAddress($email);     

		$mail->WordWrap = 50;                            
		$mail->isHTML(true);                             
		$mail->Subject = $subject;
		$mail->Body    = $msg;
		//$mail->SMTPDebug = 3;
		if($mail->send()) {
			return true;
			
		}
		return false;
	}
	
	public function sendMail($email_address,$subject,$body,$header,$name = NULL){
		$msg = '<div style="width:100%;max-width:700px;margin:0 auto;min-height:300px; box-shadow: 0 1px 2px rgba(0,0,0,.2);;border:1px solid #ddd;">
				<div style="width:100%;background: none repeat scroll 0% 0% #283442;font-size:20px;color:#fff;"><div style="padding:20px">'.$header.'</div></div>
				<div style="margin:20px;">';
				if($name){
					$msg .= '<b>Dear '.ucfirst($name).',</b><br/><br/>';
					}
				$msg .= $body.'</div>
				<div style="border-top:1px solid #ddd;width:100%;font-size:11px;"><div style="padding:20px">This email was sent from an automated system which doesn\'t accept incoming mail. Any replies to this address won\'t be received, so do not reply back.</div></div>
				</div> ';
		if($this->send($email_address,$subject,$msg)){
			return true;
		}
		return false;
	}
}