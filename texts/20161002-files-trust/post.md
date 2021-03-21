- slug:dont-trust-files-users-upload-to-your-server
- date:Oct 2, 2016 15:29
# Don't trust files users upload to your server
<p>There are plenty of attack vectors to your server (app, data) based on uploaded files. Once an attacker uploaded a malicious file he has a chance to execute it because it is on your server already. </p>
<p><!--more--></p>
<p>Many of modern apps support user file uploading. And that is the basement for an attacker to make his action plan. </p>
<p>The thing is that many developers are under huge pressure of deadlines, multitasking between projects etc. Sum it up with a lack of security expertise and here we go, new data leaks and other business fails are coming up weekly.</p>
<h2>So what a developer can do to protect the system from being hacked through the malicious files? </h2>
<p>First things first. Never trust anything user passes into the system. </p>
<p>The file itself has many places where attacker can breach the system:</p>
<ul>
<li><strong>name</strong><br />
 If app trusts the name, we can end up with files like “hack.exe” or “backdoor.php” on our system;</li>
<li><strong>type (mime type)</strong><br />
 there are two ways of detecting the type of the file: one it to inspect the file contents and two is to trust what user says the file is. If we trust user we can end up with malicious files on the disk because the user said it was an image.</li>
<li><strong>contents</strong><br />
 there are plenty of places where to hide the malicious code in the file’s content. Not always developer has a chance to detect it. If the file was not inspected we can end up with that code is stored on our disk and possibly executed later.</li>
<li><strong>size</strong><br />
 If our code does not check file size then it is relatively easy to upload thousands of files and eat all of our disk space causing the halt of the system.</li>
</ul>
<p> </p>
<p>Example which can happen in Laravel based application:</p>

```php
<?php

/*
*  Controller handles Uploading files
*/
class ImageUploadController extends Controller
{
    
  	/*
	* Accept user's file, validate it
	*/
	  public function upload(Request $request)
	  {

		// GOOD CODE 
		// Here we rely on built-in Laravel's validation of file types
		// The validator does not trust the mimetype which user gave us
		// Intead it uses PHP binary library called  "PECL fileinfo" to detect mimetype from file's content
		  try {
			  $this->validate($request, [
				  'image' => 'file|image|required',
			  ]);
		  } catch(ValidationException $e) {
			  return $e->validator->getMessageBag()->all();
		  }

		// BAD CODE
		// Since the validation is passed and file is actually an image - what wrong  can happen if I just save the same name as user gave it?
		// The thing is - PHP code can be hidden inside the valid image.
		//
		// Here we have a dangerous line which saves a file with the same name that client gave us
		// For example if image is called "face.php" it will saved with the same name on disk
		// If that happens attacker can execute the file by calling it from the browser like URL/images/face.php
		  $saved_relative_path = $request->file('image')
									       ->storeAs("images",$request->file('image')->getClientOriginalName());

		$url                 = url($saved_relative_path);

		return "saved to <a href='" . $url . "'>$url</a>";
	  }
}

?>
```
<div class="code-embed-infos"> <span class="code-embed-name">Unsafe file upload</span> </div> 
<p> </p>
<p>To improve security developer can do few things:</p>
<ul>
<li><strong>Make up a name</strong> for any new file so attacker has no chance to pass the malicious name;</li>
<li><strong>Detect (validate) file type</strong> based on it’s content. If it is an image – we should (not must but rather should) re-save it using our encoder. That is often a good way to resize an image before saving to conform with UI requirements. But also this is much safer to re-save image with all the meta information stripped out and just to make sure that the image has no built-in malicious code.</li>
<li><strong>Move an uploaded file</strong> to the dedicated file server. That will eliminate the risk of executing it on the app server. Often image is moved to cloud storage for safety and for better delivery performance (cloud often used with CDN services).</li>
<li><strong>Validate file size</strong> and keep control on how many disk space each user can take for his files. Some common approach is to remove older file when newer is uploaded (good for avatar images) or just keep a record of all uploaded files and associate some validation rule with it.</li>
</ul>
<h2>Developer must be aware of common attacking vectors</h2>
<p>To improve the system’s security developers should learn how to hack it. That is a good thing to fool around with the system on staging server trying to hack it. Knowing how to break things will improve the system a developer makes. </p>
<p>I think these links are a good starting point to understand how unsafe file uploads can increase risk of being hacked:</p>
<ul>
<li><a href="https://www.sans.org/reading-room/whitepapers/testing/web-application-file-upload-vulnerabilities-36487">Web Application File Upload Vulnerabilities</a></li>
<li><a href="https://www.owasp.org/index.php/Unrestricted_File_Upload"><span dir="auto">Unrestricted File Upload</span></a></li>
<li><a href="https://phocean.net/2013/09/29/file-upload-vulnerabilities-appending-php-code-to-an-image.html">File upload vulnerabilities : appending PHP code to an image</a></li>
</ul>
<p> </p>
