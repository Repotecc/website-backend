<?php 

    header("Access-Control-Allow-Origin: *"); 
    header("Access-Control-Allow-Headers: Content-Type"); 
    header("Content-Type: application/json"); 
    $rest_json = file_get_contents("php://input");

    $_POST = json_decode($rest_json, true); 
    $errors = array(); 

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    // If necessary, modify the path in the require statement below to refer to the
    // location of your Composer autoload.php file.
    require 'vendor/autoload.php';

    \Dotenv\Dotenv::createImmutable(__DIR__)->load();


// Replace sender@example.com with your "From" address.
// This address must be verified with Amazon SES.
$sender = 'ayomide@repotecc.com';
$senderName = 'Repotecc';

    $recipient = 'info@repotecc.com';

// Replace smtp_username with your Amazon SES SMTP user name.
$usernameSmtp =  $_ENV['USERNAME_SMTP']; 

// // Replace smtp_password with your Amazon SES SMTP password.
$passwordSmtp = $_ENV['PASSWORD_SMTP']; 





// Specify a configuration set. If you do not want to use a configuration
// set, comment or remove the next line.
$configurationSet = 'ConfigSet';

// If you're using Amazon SES in a region other than US West (Oregon),
// replace email-smtp.us-west-2.amazonaws.com with the Amazon SES SMTP
// endpoint in the appropriate region.
$host = 'ssl://email-smtp.eu-west-2.amazonaws.com';
$port = 443;

$bodyText =  "Email Test\r\nThis email was sent through the
    Amazon SES SMTP interface using the PHPMailer class.";

// The HTML-formatted body of the email
$bodyHtml = '<h1>Email Test</h1>
    <p>This email was sent through the
    <a href="https://aws.amazon.com/ses">Amazon SES</a> SMTP
    interface using the <a href="https://github.com/PHPMailer/PHPMailer">
    PHPMailer</a> class.</p>';

    if ($_SERVER['REQUEST_METHOD'] === "POST") { 
        if (empty($_POST['contact_email'])) {
            $errors[] = 'Email is empty';
            
        } else { 
            //$email = $_POST['email'];
            $first_name = $_POST["contact_fname"];
            $last_name = $_POST["contact_lname"];
            $contact_phone = $_POST["contact_phone"];
            $email = $_POST["contact_email"];
            $contact_title = $_POST["contact_title"];
            $contact_message = $_POST["contact_message"];
            
            // validating the email 
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
                $errors[] = 'Invalid email'; 
            } 
                
        }

        if (empty($_POST['contact_message'])) {
            
            $errors[] = 'Message is empty';
        } else {
            $message = $_POST['contact_message']; 
                
        } 
        
        if (empty($errors)) {
                    
            $date = date('j, F Y h:i A'); 
            $bodyHtml = " <html> 
                                <head> 
                                    <title>
                                        $email is contacting you
                                    </title>
                                </head> 
                                <body style=\"background-color:#fafafa;\"> 
                                    <div style=\"padding:20px;\"> 
                                        Date: <span style=\"color:#888\">$date</span> 
                                        <br> FullName: <span style=\"color:#888\">$first_name</span>
                                                       <span style=\"color:#888\">$last_name</span>
                                        <br> Email: <span style=\"color:#888\">$email</span>  
                                        <br> Contact Phone: <span style=\"color:#888\">$contact_phone</span>
                                        <br> Message Title: <span style=\"color:#888\">$contact_title</span>
                                        <br> Message <span style=\"color:#888\">$contact_message</span>  
                                        
                                    </div> 
                                </body> 
                            </html>"; 
            $headers = 	'From: Contact Form <info@repotecc.com>' . "\r\n" . 
                        "Reply-To: $email" . "\r\n" . 
                        "MIME-Version: 1.0\r\n" . 
                        "Content-Type: text/html; charset=iso-8859-1\r\n";
                        
            $to = 'info@repotecc.com'; 
            $subject = 'Contact Us'; 



            $mail = new PHPMailer(true);

            try {
                // Specify the SMTP settings.
                $mail->isSMTP(true);
                $mail->setFrom($sender, $senderName);
                $mail->Username   = $usernameSmtp;
                $mail->Password   = $passwordSmtp;
                $mail->Host       = $host;
                $mail->Port       = $port;
                $mail->SMTPAuth   = true;
                $mail->SMTPSecure = 'tls';
                // $mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);

                // Specify the message recipients.
                $mail->addAddress($recipient);
                // You can also add CC, BCC, and additional To recipients here.

                // Specify the content of the message.
                $mail->isHTML(true);
                $mail->Subject    = $subject;
                $mail->Body       = $bodyHtml;
                $mail->AltBody    = $bodyText;
                $mail->Send();
                // echo "Email sent!" , PHP_EOL;
                $sent = true;
            } catch (phpmailerException $e) {
                echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
            } catch (Exception $e) {
                echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
            }

            ?>
            <?php
                
            // if (mail(
            //     $to, $subject, $emailBody, $headers)) { 
            //         $sent = true;
            // } 
                
        } 
    }
        ?> 



<?php if (!empty($errors)) : ?> 
{ 
    "status": "fail",
        "error": <?php echo json_encode($errors) ?> 
} 
    
<?php endif; ?> 
<?php 
    if (isset($sent) && $sent === true) : ?> {
         "status": "success",
          "message": "Your data was successfully submitted" 
    }
    <?php endif; ?>
