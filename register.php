<?php
require __DIR__.'/php/bootstrap.php'; csrf_check();
$errors=[]; $questions=questions();
if($_SERVER['REQUEST_METHOD']==='POST') {
 if(!captcha_verify()) $errors['captcha']='Please complete the CAPTCHA verification.';
 if(($_POST['honeypot']??'')!=='') exit;
 $id=trim($_POST['id_number']??''); $username=trim($_POST['username']??'');
 $first=trim($_POST['first_name']??''); $middle=trim($_POST['middle_name']??''); $family=trim($_POST['family_name']??'');
 $birth=$_POST['birthdate']??''; $email=strtolower(trim($_POST['email']??''));
 $age=$birth? (int)(new DateTime($birth))->diff(new DateTime('today'))->y:0;
 if(!preg_match('/^\d{4}-\d{4}$/',$id)) $errors['id_number']='ID Number must follow the format xxxx-xxxx.';
 foreach(['first_name'=>$first,'middle_name'=>$middle,'family_name'=>$family] as $k=>$v) if($k!=='middle_name'||$v!=='') {
  if(!preg_match('/^[A-Z][a-z]+(?: [A-Z][a-z]+)*$/',$v)) $errors[$k]='Use Title Case letters with single spaces only.';
  if(mb_strlen($v)>20) $errors[$k]='Name fields may contain at most 20 characters.';
 }
 if(!$birth || $birth>date('Y-m-d') || $age<18) $errors['birthdate']='You must be at least 18 years old to register.';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>100) $errors['email']='Please enter a valid email address.';
 if(!preg_match('/^[A-Za-z][A-Za-z0-9]{5,15}$/',$username)) $errors['username']='Username must start with a letter and contain 6–16 letters or digits.';
 $pass=$_POST['password']??''; if(strlen($pass)<8||strlen($pass)>64||!preg_match('/[A-Z]/',$pass)||!preg_match('/[a-z]/',$pass)||!preg_match('/\d/',$pass)) $errors['password']='Use 8–64 characters with uppercase, lowercase and a number.';
 if($pass!==($_POST['confirm_password']??'')) $errors['confirm_password']='Passwords do not match.';
 if(empty($_POST['consent'])) $errors['consent']='You must agree to the Privacy Notice to register.';
 if(!preg_match('/^09\d{9}$/',$_POST['emergency_contact']??'')) $errors['emergency_contact']='Contact number must be 11 digits and start with 09.';
 if(!in_array($_POST['blood_type']??'', ['A+','A-','B+','B-','AB+','AB-','O+','O-'],true)) $errors['blood_type']='Please select a valid blood type.';
 if(!preg_match('/^\d{2}-\d{9}-\d$/',$_POST['philhealth']??'')) $errors['philhealth']='PhilHealth Number must follow the format xx-xxxxxxxxx-x.';
 if(!preg_match('/^[A-Za-z, ]{0,100}$/',$_POST['allergies']??'')) $errors['allergies']='Allergies may contain letters, commas and spaces only.';
 foreach($questions as $code=>$label) {
  $a=trim($_POST['answer_'.$code]??'');
  if(!preg_match('/^[A-Za-z]+(?: [A-Za-z]+)*$/',$a)||strlen($a)<2||strlen($a)>50) $errors['answer_'.$code]='Answer must contain 2–50 letters and single spaces.';
 }
 $dup=db()->prepare('SELECT id_number,email,username FROM users WHERE id_number=? OR email=? OR username=?'); $dup->execute([$id,$email,$username]); $d=$dup->fetch();
 if($d) { if($d['id_number']===$id)$errors['id_number']='This ID Number is already registered.'; if($d['email']===$email)$errors['email']='This email is already registered.'; if($d['username']===$username)$errors['username']='This username is already taken.'; }
 if(!$errors) {
  $address=['purok_street','barangay','city','province','country','zip_code'];
  foreach($address as $f) if(trim($_POST[$f]??'')==='') $errors[$f='address']='Please complete all address fields.';
 }
 if(!$errors) {
  $q=db()->prepare('INSERT INTO users(id_number,first_name,middle_name,family_name,birthdate,age,purok_street,barangay,city,province,country,zip_code,email,username,password_hash,philhealth_enc,blood_type,emergency_contact,known_allergies,consent_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
  $q->execute([$id,$first,$middle?:null,$family,$birth,$age,$_POST['purok_street'],$_POST['barangay'],$_POST['city'],$_POST['province'],$_POST['country'],$_POST['zip_code'],$email,$username,password_hash($pass,PASSWORD_DEFAULT),encrypt_field($_POST['philhealth']),$_POST['blood_type'],$_POST['emergency_contact'],$_POST['allergies']??null]);
  $uid=(int)db()->lastInsertId();
  foreach($questions as $code=>$label) db()->prepare('INSERT INTO auth_answers(user_id,question_code,answer_hash) VALUES(?,?,?)')->execute([$uid,$code,password_hash(mb_strtolower(trim($_POST['answer_'.$code])),PASSWORD_DEFAULT)]);
  log_event('REGISTERED',$username,$uid);
  if(send_otp($uid,$email,'register')) { $_SESSION['pending_user']=$uid; $_SESSION['pending_purpose']='register'; redirect('otp.php'); }
  $errors['form']='Account saved, but email delivery failed. Check SMTP configuration and ask for a new verification code.';
 }
}
page_header('Create account');
?>
<h1 class="page-title">Create your secure account</h1><p class="muted">Fields marked * are required. Use fictional data while developing and testing.</p>
<form method="post" class="panel form-grid" novalidate>
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input class="hp" name="honeypot" tabindex="-1" autocomplete="off">
<?php if($errors) echo '<div class="error full">'.e(reset($errors)).'</div>'; ?>
<?php
$fields=[['id_number','ID Number*','xxxx-xxxx'],['first_name','First Name*','Juan'],['middle_name','Middle Name optional',''],['family_name','Family Name*','Dela Cruz'],['birthdate','Birthdate*',''],['email','Email*','name@example.com'],['username','Username*','6–16 letters or digits'],['password','Password*','Create a strong password'],['confirm_password','Re-enter Password*','Repeat password']];
foreach($fields as [$name,$label,$ph]) { $type=($name==='birthdate'?'date':(str_contains($name,'password')?'password':'text')); echo '<label>'.e($label).'<input name="'.e($name).'" type="'.$type.'" placeholder="'.e($ph).'" value="'.e($_POST[$name]??'').'" required></label><small class="field-error">'.e($errors[$name]??'').'</small>'; }
?>
<label>PhilHealth Number*<input name="philhealth" placeholder="xx-xxxxxxxxx-x" required></label><small></small>
<label>Blood Type*<select name="blood_type" required><option value="">Select blood type</option><?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $v) echo '<option>'.e($v).'</option>'; ?></select></label><small></small>
<label>Emergency Contact Number*<input name="emergency_contact" placeholder="09xxxxxxxxx" required></label><small></small>
<label>Known Allergies optional<input name="allergies" maxlength="100"></label><small></small>
<?php foreach(['purok_street'=>'Purok / Street','barangay'=>'Barangay','city'=>'Municipality / City','province'=>'Province','country'=>'Country','zip_code'=>'ZIP Code'] as $n=>$l) echo '<label>'.e($l).'*<input name="'.e($n).'" required></label><small></small>'; ?>
<?php foreach($questions as $code=>$q) echo '<label class="full">'.e($q).'<input name="answer_'.e($code).'" required></label><small></small>'; ?>
<div class="full"><label class="check"><input type="checkbox" name="consent" value="1"> I have read the <a href="privacy.php" target="_blank">Privacy Notice</a> and agree to the collection and processing of my personal data in accordance with RA 10173.</label></div>
<div class="full"><div class="g-recaptcha" data-sitekey="<?=e(app_config()['captcha']['site_key'])?>"></div><script src="https://www.google.com/recaptcha/api.js" async defer></script></div>
<div class="full"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button class="btn primary">Create account</button></div>
</form>
<?php page_footer(); ?>
