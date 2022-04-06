<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\AdminSettings;
use App\Models\Notifications;
use App\Models\Categories;
use App\Models\UsersReported;
use App\Models\ImagesReported;
use App\Models\Images;
use App\Models\Stock;
use App\Models\CollectionsImages;
use App\Helper;
use App\Models\PaymentGateways;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;
use Image;
use App\Models\Purchases;
use App\Models\Deposits;
use App\Models\Withdrawals;
use Mail;
use App\Libraries\Space;
use ZipArchive;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{

	public function __construct(AdminSettings $settings)
	{
		$this->settings = $settings::first();
	}
	// START
	public function admin()
	{

		return view('admin.dashboard');
	} //<--- END METHOD


	public function mailTemplates()
	{
		$data      = DB::table('mail_templates')->orderBy('id', 'desc')->paginate(20);
		return view('admin.mail-templates')->withData($data);
	}

	public function editTemplate($id)
	{
		$data = DB::table('mail_templates')->find($id);
		return view('admin.edit-template')->with('data', $data);
	}

	public function singleTemplateByType($type)
	{		
		$data = DB::table('mail_templates')
				->where('type', $type)
				->first();
		return $data;
	}

	public function updateTemplate(Request $request)
	{
		$count = DB::table('mail_templates')
				->where('id', '!=', $request->id)
				->where('type', $request->type)
				->count();
		if ($count > 0) {
			\Session::flash('info_message', 'Submission Failed, Type Already Exits');
		}else{
			$update = DB::table('mail_templates')
						->where('id', $request->id)
						->update(
							array(
								'type' => $request->type,
								'template_subject' => $request->template_subject,
								'template_body' => $request->template_body
							)
						);
			if($update){
				\Session::flash('success_message', 'Data Saved Successfully');
			}else{
				\Session::flash('info_message', 'Nothing Changes');
			}
		}

		return redirect('panel/admin/mail-template');
	}

	public function addTemplate()
	{
		return view('admin.add-template');
	}

	public function storeTemplate(Request $request)
	{
		$count = DB::table('mail_templates')
				->where('type', $request->type)
				->count();
		if ($count > 0) {
			\Session::flash('info_message', 'Submission Failed, Type Already Exits');
		}else{
			$values = array(
				'type' => $request->type,
				'template_subject' => $request->template_subject,
				'template_body' => $request->template_body,
			);
			$insert = DB::table('mail_templates')->insert($values);
			if($insert){
				\Session::flash('success_message', 'Data Saved Successfully');
			}else{
				\Session::flash('info_message', 'Submission Failed');
			}
		}
		
		return redirect('panel/admin/mail-template');
	}

	public function sendMail()
	{
		return view('admin.send-mail');
	}

	
	public function sendMailSubmit(Request $request)
	{
		$mail_to	= $request->mail_to;
		$mail_subject	= $request->mail_subject;
		$mail_details	= $request->mail_body;
		$result = $this->sendMailAction($mail_to, $mail_subject, $mail_details);
		if($result == 1){
			\Session::flash('success_message', 'Mail Sent Successfully');
		}else{
			\Session::flash('info_message', 'Mail Sending Failed');
		}
		return redirect('panel/admin/mail-send');
	}

	public function sendMailAction($mail_to, $mail_subject, $mail_details) {

		$_title_site    = env('APP_NAME');
		$_email_noreply = env('MAIL_USERNAME');

		\Mail::send('emails.basic-template', array(
			'title' => $mail_subject,
			'description' => $mail_details
		),
		 function($message) use (
			$mail_to,
			$mail_subject,
			$_title_site,
			$_email_noreply
		 ) {
			$message->from($_email_noreply, $_title_site);
			$message->subject($mail_subject);
			$message->to($mail_to, 'anonymous');

        });

		return 1;
		
	 }

	public function basic_email() {

		$_username      = 'admin';
	    $_email_user    = 'harun.bdtask@gmail.com';
		$_title_site    = env('APP_NAME');
		$_email_noreply = env('MAIL_USERNAME');

		\Mail::send('emails.basic-template', array(
			'title' => 'Withdrawal Request Cancelled',
			'description' => 'We have been cancelled your withdrawal request due to your invalid information'
		),
		 function($message) use (
				 $_username,
				 $_email_user,
				 $_title_site,
				 $_email_noreply
		 ) {
                $message->from($_email_noreply, $_title_site);
                $message->subject('Test Mail');
                $message->to($_email_user,$_username);
        });
		
		echo 200;exit;

		
	 }


	//pending image start
	public function pendingImagesList(Request $request)
	{
		$response = array();
		## Read value
		@$draw = $request->draw;
		@$start = $request->start;
		@$rowperpage = $request->length; // Rows display per page

		$records	= DB::table('uploaded_files as a')
			->select('a.*', 'b.username')
			->leftjoin('users as b', 'b.id', '=', 'a.user_id')
			->whereIn('a.extension', array('png', 'jpg', 'jpeg', 'gif'))
			->where('a.status', 'pending')
			->skip($start)->take($rowperpage)
			->get();
		## Total number of records without filtering
		// $totalRecords = $records->count();
		$totalRecords = DB::table('uploaded_files')->where('status', 'pending')->whereIn('extension', array('png', 'jpg', 'jpeg', 'gif'))->count();
		## Total number of record with filtering
		$totalRecordwithFilter = $totalRecords;
		// echo count($records);exit;
		if ($totalRecords > 0) {
			$i = 1;
			foreach ($records as $k => $item) {
				$sl = $start + $i;

				$approve = '<button data-id="' . $item->token_id . '" id="approved" class="btn btn-sm btn-success" >Approve</button>';
				$approve .= '<input type="hidden" id="token_id" value="' . $item->token_id . '">';

				$image = '<img src="https://piktask.sgp1.digitaloceanspaces.com/images/' . $item->original_file . '" alt="img" height="50px" width="50px">';

				$status = '<span class="label label-warning">' . $item->status . '</span>';

				$reject = '';
				$reject .= '<a href="' . url('panel/admin/reject-image', $item->uploaded_file_id) . '"class="btn btn-warning btn-sm padding-btn">';
				$reject .= 'Reject</a>';

				$rejected = '<button data-id="' . $item->token_id . '" id="rejected" class="btn btn-sm btn-danger padding-btn" >Reject</button>';

				$details = '';
				$details .= '<a href="' . url('panel/admin/pending-images', $item->uploaded_file_id) . '"class="btn btn-info btn-sm padding-btn">';
				$details .= 'Details</a>';

				$data[] = array(
					'sl'		=> $sl,
					'image'		=> $image,
					'title' 	=> $item->title,
					'extention' => $item->extension,
					'size' 		=> $item->size,
					'date' 		=> $item->featured_date,
					'uploaded_by' => $item->username,
					'status' 	=> $status,
					'approve' 	=> $approve,
					'reject' 	=> $reject,
					'rejected' 	=> $rejected,
					'details' 	=> $details
				);
				$i++;
			}
		} else {
			$data = [];
		}
		## Response
		$response = array(
			"draw"				=> $draw,
			"recordsTotal"		=> $totalRecords,
			"recordsFiltered"	=> $totalRecordwithFilter,
			"data"				=> $data
		);
		return $response;
	}

	public function pendingImages()
	{
		$data	= DB::table('uploaded_files as a')
			->select('a.*', 'b.name')
			->leftjoin('users as b', 'b.id', '=', 'a.user_id')
			->whereIn('a.extension', array('png', 'jpg', 'jpeg', 'gif'))
			->where('a.status', 'pending')
			->get();
		return view('admin.pending_images')->withData($data);
	}

	public function rejectPendingImages($id)
	{
		$data	= DB::table('uploaded_files')
			->where('uploaded_file_id', $id)
			->first();
		return view('admin.single_image_reject')->withData($data);
	}

	public function singleImageInfo($id)
	{
		$data	= DB::table('uploaded_files')
			->where('uploaded_file_id', $id)
			->first();
		return view('admin.single_image_info')->withData($data);
	}

	public function updateSingleImageInfoImage(Request $request)
	{
		#file upload in space
		$file = $request->file('filename');
		$filename = $file->getClientOriginalName();
		$filesize = $file->getSize();
		$fileextension = $file->getClientOriginalExtension();
		$filepath = $file->getRealPath();
		echo '<pre>';
		print_r($file);
		exit;
		if (!empty($filepath)) {
			$sizes = array(1300 => 1300, 235 => 235);
			$file_location = $this->do_upload_file($filename, $filesize, $filepath, 'images', 'public-read');
			$image_name = explode('/', $file_location[0]);
			$image_name = end($image_name);
			$base_path = 'https://piktask.sgp1.digitaloceanspaces.com/';
			$payment_slip = $base_path . '/' . 'images/' . $image_name;
			return 1;
		} else {
			return 0;
		}
	}

	public function rejectSingleImage(Request $request)
	{
		$token_id	= $request->token_id;
		$pn	= $request->reason_title;
		// $pd	= $request->reason_description;
		$n = array_key_last($pn);
		for ($i = 0; $i <= $n; $i++) {
			if (!empty($pn[$i])) {
				$report_category_id        	= $pn[$i];
				// $description	= $pd[$i];
				$reason_info = array(
					'report_category_id' => $report_category_id,
					// 'description'	=> $description,
					'token_id'		=> $token_id,
				);
				// print_r($reason_info);
				$insert = DB::table('report_reasons')->insert($reason_info);
			}
		}
		// exit;
		$update = DB::table('uploaded_files')
			->where('token_id', $token_id)
			->update(
				array(
					'status' => 'rejected'
				)
			);
		// \Session::flash('warning_message', 'Successfully Rejected!');
		// return redirect('panel/admin/pending-images');

		$info['status']		= 'rejected';
		$info['message']	= 'Successfully Rejected!';
		$info['url']		= url('panel/admin/pending-images');
		echo json_encode($info);
		exit;
	}

	public function updateSingleImageInfo(Request $request)
	{
		if ($request->status == 'rejected') {

			$info['status']		= 'rejected';
			$info['message']	= '';
			$info['url']		= url('panel/admin/reject-image/' . $request->uploaded_file_id);
			echo json_encode($info);
			exit;
		} else {
			$datalist	= DB::table('uploaded_files')
				->where('token_id', $request->token_id)
				->get();
			if (!empty($datalist)) {
				$row	= DB::table('uploaded_files')
					->where('token_id', $request->token_id)
					->whereIn('extension', array('png', 'jpg', 'jpeg', 'gif'))
					->first();
				if (count($datalist) > 1) {
					foreach ($datalist as $data) {
						#create file in directory
						$url = 'https://piktask.sgp1.digitaloceanspaces.com/images/' . $data->original_file;
						$dir =  public_path('zip_temp');
						is_dir($dir) || @mkdir($dir) || die("Can't Create folder");
						copy($url, $dir . DIRECTORY_SEPARATOR . $data->original_file);
					}
					#create zip file from directory
					$zip = new ZipArchive;
					$fileName = 'zip_' . time() . uniqid() . '.zip';
					$zip_dir = public_path('zip_temp/' . $fileName);
					if ($zip->open($zip_dir, ZipArchive::CREATE) === TRUE) {
						$files = \File::files(public_path('zip_temp'));
						foreach ($files as $key => $value) {
							$file = basename($value);
							$zip->addFile($value, $file);
						}
						$zip->close();
					}
					if (!empty($files)) {
						$filesize = 1500 * 1500; // 1MB
						$file_location = $this->do_upload_file($fileName, $filesize, $zip_dir, 'images', 'private');
						$image_name = explode('/', $file_location[0]);
						$image_name = end($image_name);
						$base_path = 'https://piktask.sgp1.digitaloceanspaces.com';
						$file_url = $base_path . '/' . 'images/' . $image_name;
					}
					#delete file from directory
					$files = glob($dir . '/*');
					foreach ($files as $file) {
						if (is_file($file)) {
							unlink($file);
						}
					}

					$values = array(
						'status' => 'active',
						'title' => $row->title,
						'preview' => $row->original_file,
						'original_file' => $image_name,
						'extension' => $row->extension,
						'token_id' => $row->token_id,
						'original_name' => $row->original_name,
						'tags' => $row->tags,
						'categories_id' => $row->categories_id,
						'user_id' => $row->user_id,
						'height' => $row->height,
						'width' => $row->width,
						'createdAt' => date('Y-m-d H:i:s'),
						'hasVector' => 1,
					);
					$insert = DB::table('images')->insert($values);
					if ($insert) {
						foreach ($datalist as $data_value) {
							#delete file from space
							if ($data_value->extension != 'png' & $data_value->extension != 'jpg' & $data_value->extension != 'jpeg' & $data_value->extension != 'gif') {
								$this->delete_space_file('images/' . $data_value->original_file);
								$update = DB::table('images')
									->where('token_id', $row->token_id)
									->update(
										array(
											'extension' => $data_value->extension
										)
									);
							}
						}
						DB::table('uploaded_files')->where('token_id', $request->token_id)->delete();
						$info['status']		= 'approved';
						$info['message']	= 'Successfully Approved!';
						$info['url']		= url('panel/admin/pending-images');
						echo json_encode($info);
						exit;
						\Session::flash('success_message', 'Successfully Approved!');
						return redirect('panel/admin/pending-images');
					}
				} else {
					$values = array(
						'status' => 'active',
						'title' => $row->title,
						'preview' => $row->original_file,
						'original_file' => $row->original_file,
						'extension' => $row->extension,
						'token_id' => $row->token_id,
						'original_name' => $row->original_name,
						'tags' => $row->tags,
						'categories_id' => $row->categories_id,
						'user_id' => $row->user_id,
						'height' => $row->height,
						'width' => $row->width,
						'createdAt' => date('Y-m-d H:i:s'),
					);
					$insert = DB::table('images')->insert($values);
					if ($insert) {
						DB::table('uploaded_files')->where('token_id', $request->token_id)->delete();
						$info['status']		= 'approved';
						$info['message']	= 'Successfully Approved!';
						$info['url']		= url('panel/admin/pending-images');
						echo json_encode($info);
						exit;
						\Session::flash('success_message', 'Successfully Approved!');
						return redirect('panel/admin/pending-images');
					}
				}
			}
		}
	}

	public function resizeFile()
	{
		#resize file for preview from url
		$image_url = 'https://piktask.sgp1.digitaloceanspaces.com/images/' . $data->original_file;
		$percent = 0.2;
		header('Content-type: image/jpeg');
		list($width, $height) = getimagesize($image_url);
		$new_width = $width * $percent;
		$new_height = $height * $percent;
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image = imagecreatefromjpeg($image_url);
		$resample = imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		$preview_name = time() . uniqid() . '.' . $data->extension;
		imagejpeg($image_p, public_path('zip_temp') . '/' . $preview_name);
	}

	function do_upload_file($filename, $filesize, $filepath, $folder, $access)
	{
		$this->spaceobj = new Space();
		$k = 0;
		$sizes = array(1300 => 1300, 235 => 235);
		foreach ($sizes as $w => $h) {
			$files[] = $this->resize_file($filepath, $filename, $folder, $access);
			$k++;
		}
		sleep(1);
		return $files;
	}
	function resize_file($filepath, $filename, $folder, $access)
	{
		$save_as = $folder . '/' . $filename;
		$this->spaceobj->upload_to_space($filepath, $access, $save_as);
		return $filename;
	}
	function delete_space_file($file_url)
	{
		// $file_url = 'zip/Example.zip';
		$this->spaceobj = new Space();
		$this->spaceobj->delete_space_file($file_url);
		return 1;
	}
	//
	#	pending image end
	//

	//withdrawals start
	public function pendingWithdrawalsList(Request $request)
	{
		$response = array();
		## Read value
		@$draw = $request->draw;
		@$start = $request->start;
		@$rowperpage = $request->length; // Rows display per page

		$records	= DB::table('withdrawals as a')
			->select('a.*', 'b.username')
			->leftjoin('users as b', 'b.id', '=', 'a.user_id')
			->where('a.status', 'pending')
			->skip($start)->take($rowperpage)
			->get();
		## Total number of records without filtering
		$totalRecords = DB::table('withdrawals')->where('status', 'pending')->count();
		## Total number of record with filtering
		$totalRecordwithFilter = $totalRecords;
		// echo count($records);exit;
		if ($totalRecords > 0) {
			$i = 1;
			foreach ($records as $k => $item) {
				$sl = $start + $i;

				$approve = '<button data-id="' . $item->id . '" id="approved" class="btn btn-sm btn-success" >Approve</button>';

				$reject = '<button data-id="' . $item->id . '" id="rejected" class="btn btn-sm btn-danger" >Reject</button>';

				$status = '<span class="label label-warning">' . $item->status . '</span>';

				$data[] = array(
					'sl'			=> $sl,
					'amount'		=> $item->amount,
					'gateway' 		=> $item->gateway,
					'account' 		=> $item->account,
					'date'			=> $item->date,
					'uploaded_by'	=> $item->username,
					'status' 		=> $status,
					'approve' 		=> $approve,
					'reject' 		=> $reject
				);
				$i++;
			}
		} else {
			$data = [];
		}
		## Response
		$response = array(
			"draw"				=> $draw,
			"recordsTotal"		=> $totalRecords,
			"recordsFiltered"	=> $totalRecordwithFilter,
			"data"				=> $data
		);
		return $response;
	}

	public function pendingWithdrawals()
	{

		return view('admin.pending_withdrawals');
	}

	public function paidWithdrawals()
	{


		$data = Withdrawals::where('status', 'paid')->orderBy('id', 'DESC')->paginate(50);
		return view('admin.withdrawals', ['data' => $data, 'settings' => $this->settings]);
	}

	public function rejectedWithdrawals()
	{

		$data = Withdrawals::where('status', 'rejected')->orderBy('id', 'DESC')->paginate(50);
		return view('admin.withdrawals', ['data' => $data, 'settings' => $this->settings]);
	}

	public function updatePendingWithdrawals(Request $request)
	{
		$admin = DB::table('admin_settings')
				->first();
		$vat = $admin->vat;
		$row = DB::table('withdrawals as a')
			->select('a.*', 'b.username', 'b.email')
			->leftjoin('users as b', 'b.id', '=', 'a.user_id')
			->where('a.id', $request->id)
			->first();
		$mail_to = $row->email;
		$username = $row->username;
		if (!empty($row)) {

			if ($request->status == 'rejected') {
				//Balance Add in user wallet
				DB::table('users')->where('id', $row->user_id)->increment('balance', $row->amount);
				//reject reason update
				$update = DB::table('withdrawals')
					->where('id', $request->id)
					->update(
						array(
							'status' => $request->status,
							'updatedAt' => date('Y-m-d H:i:s'),
							'reason' => $request->reason
						)
					);
				//mail template
				$mail_template = $this->singleTemplateByType('withdrawal_reject');
				$mail_subject = $mail_template->template_subject;
				$mail_details = str_replace('{user}', $username, $mail_template->template_body);
				//sending mail
				$result = $this->sendMailAction($mail_to, $mail_subject, $mail_details);
				//response
				$info['status']		= 'ok';
				$info['title']		= 'Rejected';
				$info['message']	= 'Successfully Rejected';
				echo json_encode($info);
				exit;
			} elseif ($request->status == 'paid') {
				$update = DB::table('withdrawals')
					->where('id', $request->id)
					->update(
						array(
							'status' => $request->status,
							'vat' => $vat,
							'updatedAt' => date('Y-m-d H:i:s'),
							'date_paid' => date('Y-m-d H:i:s')
						)
					);
				//mail template
				$mail_template = $this->singleTemplateByType('withdrawal_approve');
				$mail_subject = $mail_template->template_subject;
				$mail_details = str_replace('{user}', $username, $mail_template->template_body);
				//sending mail
				$result = $this->sendMailAction($mail_to, $mail_subject, $mail_details);
				//response
				$info['status']		= 'ok';
				$info['title']		= 'Paid';
				$info['message']	= 'Successfully Paid';
				echo json_encode($info);
				exit;
			} else {
				$info['status']		= 'error';
				$info['title']		= 'Error';
				$info['message']	= 'Invalid Request';
				echo json_encode($info);
				exit;
			}
		} else {
			$info['status']		= 'error';
			$info['title']		= 'Error';
			$info['message']	= 'Invalid Request';
			echo json_encode($info);
			exit;
		}
	}

	//
	#	withdrawal end
	//

	//
	#	report start
	//
	public function reports()
	{
		$data = DB::table('contact_us as a')
				->select('a.*', 'b.name as c_name')
				->leftjoin('contact_categories as b', 'b.id', '=', 'a.contact_categories_id')
				->orderBy('a.id', 'desc')->paginate(20);
		return view('admin.reports')->withData($data);
	}

	public function deleteReport($id)
	{
		$categories	= DB::table('contact_us')->find($id);
		if (!isset($categories)) {
			return redirect('panel/admin/reports');
		} else {
			DB::table('contact_us')->where('id', $id)->delete();
			return redirect('panel/admin/reports');
		}
	}


	// START
	public function categories()
	{

		$data      = Categories::orderBy('id', 'desc')->paginate(20);

		return view('admin.categories')->withData($data);
	} //<--- END METHOD

	public function addCategories()
	{

		return view('admin.add-categories');
	} //<--- END METHOD

	public function storeCategories(Request $request)
	{

		$temp            = 'public/temp/'; // Temp
		$path            = 'public/img-category/'; // Path General

		Validator::extend('ascii_only', function ($attribute, $value, $parameters) {
			return !preg_match('/[^x00-x7F\-]/i', $value);
		});

		$rules = array(
			'name'        => 'required',
			'slug'        => 'required|ascii_only|unique:categories',
			'thumbnail'   => 'mimes:jpg,gif,png,jpe,jpeg|dimensions:min_width=400,min_height=400',
		);

		$this->validate($request, $rules);

		if ($request->hasFile('thumbnail')) {

			#file upload in space
			$file = $request->file('thumbnail');
			$filename = $file->getClientOriginalName();
			$filesize = $file->getSize();
			$fileextension = $file->getClientOriginalExtension();
			$filepath = $file->getRealPath();
			$filenameuniq = time() . uniqid() . '.' . $fileextension;
			if (!empty($filepath)) {
				$file_location = $this->do_upload_file($filenameuniq, $filesize, $filepath, 'categories', 'public-read');
				$image_name = explode('/', $file_location[0]);
				$image_name = end($image_name);
				
			}

			// 	$extension			= $request->file('thumbnail')->getClientOriginalExtension();
			// 	$type_mime_shot		= $request->file('thumbnail')->getMimeType();
			// 	$sizeFile			= $request->file('thumbnail')->getSize();
			// 	$thumbnail			= $request->slug . '-' . str_random(32) . '.' . $extension;

			// if ($request->file('thumbnail')->move($temp, $thumbnail)) {

			// 	$image = Image::make($temp . $thumbnail);

			// 	if ($image->width() == 400 && $image->height() == 400) {

			// 		\File::copy($temp . $thumbnail, $path . $thumbnail);
			// 		\File::delete($temp . $thumbnail);
			// 	} else {
			// 		$image->fit(400, 400)->save($temp . $thumbnail);

			// 		\File::copy($temp . $thumbnail, $path . $thumbnail);
			// 		\File::delete($temp . $thumbnail);
			// 	}
			// }

		} else {
			$image_name = '';
		}


		$sql              = new Categories();
		$sql->name        = trim($request->name);
		$sql->slug        = strtolower($request->slug);
		$sql->thumbnail	  = $image_name;
		$sql->mode        = $request->mode;
		$sql->save();

		\Session::flash('success_message', trans('admin.success_add_category'));

		return redirect('panel/admin/categories');
	} //<--- END METHOD

	public function editCategories($id)
	{

		$categories = Categories::find($id);

		return view('admin.edit-categories')->with('categories', $categories);
	} //<--- END METHOD

	public function updateCategories(Request $request)
	{


		$categories		 = Categories::find($request->id);
		$temp            = 'public/temp/'; // Temp
		$path            = 'public/img-category/'; // Path General

		if (!isset($categories)) {
			return redirect('panel/admin/categories');
		}

		Validator::extend('ascii_only', function ($attribute, $value, $parameters) {
			return !preg_match('/[^x00-x7F\-]/i', $value);
		});

		$rules = array(
			'name'        => 'required',
			'slug'        => 'required|ascii_only|unique:categories,slug,' . $request->id,
			'thumbnail'   => 'mimes:jpg,gif,png,jpe,jpeg|dimensions:min_width=400,min_height=400',
		);

		$this->validate($request, $rules);

		if ($request->hasFile('thumbnail')) {

			#file upload in space
			$file = $request->file('thumbnail');
			$filename = $file->getClientOriginalName();
			$filesize = $file->getSize();
			$fileextension = $file->getClientOriginalExtension();
			$filepath = $file->getRealPath();
			$filenameuniq = time() . uniqid() . '.' . $fileextension;
			if (!empty($filepath)) {
				$file_location = $this->do_upload_file($filenameuniq, $filesize, $filepath, 'categories', 'public-read');
				$image_name = explode('/', $file_location[0]);
				$image_name = end($image_name);
				#delete old file
				$this->delete_space_file('categories/' . $categories->thumbnail);
			}

			// $extension		  = $request->file('thumbnail')->getClientOriginalExtension();
			// $type_mime_shot   = $request->file('thumbnail')->getMimeType();
			// $sizeFile		  = $request->file('thumbnail')->getSize();
			// $thumbnail		  = $request->slug . '-' . str_random(32) . '.' . $extension;

			// if ($request->file('thumbnail')->move($temp, $thumbnail)) {

			// 	$image = Image::make($temp . $thumbnail);

			// 	if ($image->width() == 400 && $image->height() == 400) {

			// 		\File::copy($temp . $thumbnail, $path . $thumbnail);
			// 		\File::delete($temp . $thumbnail);
			// 	} else {
			// 		$image->fit(400, 400)->save($temp . $thumbnail);

			// 		\File::copy($temp . $thumbnail, $path . $thumbnail);
			// 		\File::delete($temp . $thumbnail);
			// 	}

			// 	\File::delete($path . $categories->thumbnail);
			// }

		} else {
			$image_name = $categories->thumbnail;
		}

		// UPDATE CATEGORY
		$categories->name        = $request->name;
		$categories->slug        = strtolower($request->slug);
		$categories->thumbnail  = $image_name;
		$categories->mode        = $request->mode;
		$categories->save();

		\Session::flash('success_message', trans('misc.success_update'));

		return redirect('panel/admin/categories');
	} //<--- END METHOD

	public function deleteCategories($id)
	{

		$categories        = Categories::find($id);
		// $thumbnail          = 'public/img-category/' . $categories->thumbnail; // Path General

		if (!isset($categories) || $categories->id == 1) {
			return redirect('panel/admin/categories');
		} else {

			// $images_category   = Images::where('categories_id', $id)->get();

			
			#delete old file
			$this->delete_space_file('categories/' . $categories->thumbnail);
			
			// Delete Category
			$categories->delete();

			// // Delete Thumbnail
			// if (\File::exists($thumbnail)) {
			// 	\File::delete($thumbnail);
			// } //<--- IF FILE EXISTS

			// //Update Categories Images
			// if (isset($images_category)) {
			// 	foreach ($images_category as $key) {
			// 		$key->categories_id = 1;
			// 		$key->save();
			// 	}
			// }

			return redirect('panel/admin/categories');
		}
	} //<--- END METHOD

	public function settings()
	{

		return view('admin.settings');
	} //<--- END METHOD

	public function saveSettings(Request $request)
	{

		Validator::extend('sell_option_validate', function ($attribute, $value, $parameters) {
			// Count images for sale
			$imagesForSale = Images::where('item_for_sale', 'sale')->where('status', 'active')->count();

			if ($value == 'off' && $imagesForSale > 0) {
				return false;
			}

			return true;
		});

		$messages = [
			'sell_option.sell_option_validate' => trans('misc.sell_option_validate')
		];

		$rules = array(
			'title'            => 'required',
			'welcome_text' 	   => 'required',
			'welcome_subtitle' => 'required',
			'keywords'         => 'required',
			'description'      => 'required',
			'email_no_reply'   => 'required',
			'email_admin'      => 'required',
			'link_terms'      => 'required|url',
			'link_privacy'      => 'required|url',
			'link_license'      => 'url',
			'sell_option' => 'sell_option_validate'
		);

		$this->validate($request, $rules, $messages);

		$sql                      = AdminSettings::first();
		$sql->title               = $request->title;
		$sql->welcome_text        = $request->welcome_text;
		$sql->welcome_subtitle    = $request->welcome_subtitle;
		$sql->keywords            = $request->keywords;
		$sql->description         = $request->description;
		$sql->email_no_reply      = $request->email_no_reply;
		$sql->email_admin         = $request->email_admin;
		$sql->link_terms         = $request->link_terms;
		$sql->link_privacy         = $request->link_privacy;
		$sql->link_license         = $request->link_license;
		$sql->captcha             = $request->captcha;
		$sql->registration_active = $request->registration_active;
		$sql->email_verification  = $request->email_verification;
		$sql->facebook_login  = $request->facebook_login;
		$sql->twitter_login = $request->twitter_login;
		$sql->google_ads_index    = $request->google_ads_index;
		$sql->sell_option    = $request->sell_option;
		$sql->who_can_sell   = $request->who_can_sell;
		$sql->who_can_upload   = $request->who_can_upload;
		$sql->free_photo_upload   = $request->free_photo_upload;
		$sql->show_counter       = $request->show_counter;
		$sql->show_categories_index       = $request->show_categories_index;
		$sql->show_images_index    = $request->show_images_index;
		$sql->show_watermark    = $request->show_watermark;
		$sql->lightbox         = $request->lightbox;
		$sql->save();

		if ($this->settings->who_can_upload == 'all' && $request->who_can_upload == 'admin') {
			User::where('role', '<>', 'admin')->update([
				'authorized_to_upload' => 'no'
			]);
		} elseif ($this->settings->who_can_upload == 'admin' && $request->who_can_upload == 'all') {
			User::where('role', '<>', 'admin')->update([
				'authorized_to_upload' => 'yes'
			]);
		}

		\Session::flash('success_message', trans('admin.success_update'));

		return redirect('panel/admin/settings');
	} //<--- END METHOD

	public function settingsLimits()
	{

		return view('admin.limits');
	} //<--- END METHOD

	public function saveSettingsLimits(Request $request)
	{


		$sql                      = AdminSettings::first();
		$sql->result_request      = $request->result_request;
		$sql->limit_upload_user   = $request->limit_upload_user;
		$sql->daily_limit_downloads = $request->daily_limit_downloads;
		$sql->title_length        = $request->title_length;
		$sql->message_length      = $request->message_length;
		$sql->comment_length      = $request->comment_length;
		$sql->file_size_allowed   = $request->file_size_allowed;
		$sql->auto_approve_images = $request->auto_approve_images;
		$sql->downloads           = $request->downloads;
		$sql->tags_limit          = $request->tags_limit;
		$sql->description_length  = $request->description_length;
		$sql->min_width_height_image = $request->min_width_height_image;
		$sql->file_size_allowed_vector = $request->file_size_allowed_vector;

		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

		return redirect('panel/admin/settings/limits');
	} //<--- END METHOD

	public function members_reported()
	{

		$data = UsersReported::orderBy('id', 'DESC')->get();

		return view('admin.members_reported')->withData($data);
	} //<--- END METHOD

	public function delete_members_reported(Request $request)
	{

		$report = UsersReported::find($request->id);

		if (isset($report)) {
			$report->delete();
		}

		return redirect('panel/admin/members-reported');
	} //<--- END METHOD

	public function images_reported()
	{

		$data = ImagesReported::orderBy('id', 'DESC')->get();

		//dd($data);

		return view('admin.images_reported')->withData($data);
	} //<--- END METHOD

	public function delete_images_reported(Request $request)
	{

		$report = ImagesReported::find($request->id);

		if (isset($report)) {
			$report->delete();
		}

		return redirect('panel/admin/images-reported');
	} //<--- END METHOD

	public function images()
	{

		$query = request()->get('q');
		$sort = request()->get('sort');
		$pagination = 15;

		$data = Images::orderBy('id', 'desc')->paginate($pagination);

		// Search
		if (isset($query)) {
			$data = Images::where('title', 'LIKE', '%' . $query . '%')
				->orWhere('tags', 'LIKE', '%' . $query . '%')
				->orderBy('id', 'desc')->paginate($pagination);
		}

		if (isset($sort) && $sort == 'title') {
			$data = Images::orderBy('title', 'asc')->paginate($pagination);
		}

		if (isset($sort) && $sort == 'pending') {
			$data = Images::where('status', 'pending')->paginate($pagination);
		}

		if (isset($sort) && $sort == 'downloads') {
			$data = Images::join('downloads', 'images.id', '=', 'downloads.images_id')
				->groupBy('downloads.images_id')
				->orderBy(\DB::raw('COUNT(downloads.images_id)'), 'desc')
				->select('images.*')
				->paginate($pagination);
		}

		if (isset($sort) && $sort == 'likes') {
			$data = Images::join('likes', function ($join) {
				$join->on('likes.images_id', '=', 'images.id')->where('likes.status', '=', '1');
			})
				->groupBy('likes.images_id')
				->orderBy(\DB::raw('COUNT(likes.images_id)'), 'desc')
				->select('images.*')
				->paginate($pagination);
		}

		return view('admin.images', ['data' => $data, 'query' => $query, 'sort' => $sort]);
	} //<--- End Method

	public function delete_image(Request $request)
	{

		//<<<<---------------------------------------------

		$image = Images::find($request->id);

		// Delete Notification
		$notifications = Notifications::where('destination', $request->id)
			->where('type', '2')
			->orWhere('destination', $request->id)
			->where('type', '3')
			->orWhere('destination', $request->id)
			->where('type', '6')
			->get();

		if (isset($notifications)) {
			foreach ($notifications as $notification) {
				$notification->delete();
			}
		}

		// Collections Images
		$collectionsImages = CollectionsImages::where('images_id', '=', $request->id)->get();
		if (isset($collectionsImages)) {
			foreach ($collectionsImages as $collectionsImage) {
				$collectionsImage->delete();
			}
		}

		// Images Reported
		$imagesReporteds = ImagesReported::where('image_id', '=', $request->id)->get();
		if (isset($imagesReporteds)) {
			foreach ($imagesReporteds as $imagesReported) {
				$imagesReported->delete();
			}
		}

		//<---- ALL RESOLUTIONS IMAGES
		$stocks = Stock::where('images_id', '=', $request->id)->get();

		foreach ($stocks as $stock) {

			// Delete Stock
			Storage::delete(config('path.uploads') . $stock->type . '/' . $stock->name);

			// Delete Stock Vector
			Storage::delete(config('path.files') . $stock->name);

			$stock->delete();
		} //<--- End foreach

		// Delete preview
		Storage::delete(config('path.preview') . $image->preview);

		// Delete thumbnail
		Storage::delete(config('path.thumbnail') . $image->thumbnail);

		$image->delete();

		return redirect('panel/admin/images');
	} //<--- End Method

	public function edit_image($id)
	{

		$data = Images::findOrFail($id);

		return view('admin.edit-image', ['data' => $data]);
	} //<--- End Method

	public function update_image(Request $request)
	{

		$sql = Images::find($request->id);

		$rules = array(
			'title'       => 'required|min:3|max:' . $this->settings->title_length,
			'description' => 'min:2|max:' . $this->settings->description_length . '',
			'tags'        => 'required',

		);

		if ($request->featured == 'yes' && $sql->featured == 'no') {
			$featuredDate = \Carbon\Carbon::now();
		} elseif ($request->featured == 'yes' && $sql->featured == 'yes') {
			$featuredDate = $sql->featured_date;
		} else {
			$featuredDate = '';
		}

		$this->validate($request, $rules);

		$sql->title         = $request->title;
		$sql->description   = $request->description;
		$sql->tags          = $request->tags;
		$sql->categories_id = $request->categories_id;
		$sql->status        = $request->status;
		$sql->featured      = $request->featured;
		$sql->featured_date = $featuredDate;


		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

		return redirect('panel/admin/images');
	} //<--- End Method

	public function profiles_social()
	{
		return view('admin.profiles-social');
	} //<--- End Method

	public function update_profiles_social(Request $request)
	{

		$sql = AdminSettings::find(1);

		$rules = array(
			'twitter'    => 'url',
			'facebook'   => 'url',
			'linkedin'   => 'url',
			'instagram'  => 'url',
			'youtube'  => 'url',
			'pinterest'  => 'url',
		);

		$this->validate($request, $rules);

		$sql->twitter       = $request->twitter;
		$sql->facebook      = $request->facebook;
		$sql->linkedin      = $request->linkedin;
		$sql->instagram     = $request->instagram;
		$sql->youtube     = $request->youtube;
		$sql->pinterest     = $request->pinterest;

		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

		return redirect('panel/admin/profiles-social');
	} //<--- End Method

	public function google()
	{
		return view('admin.google');
	} //<--- END METHOD

	public function update_google(Request $request)
	{
		$sql = AdminSettings::first();

		$sql->google_adsense_index   = $request->google_adsense_index;
		$sql->google_adsense   = $request->google_adsense;
		$sql->google_analytics = $request->google_analytics;

		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

		return redirect('panel/admin/google');
	} //<--- End Method

	public function theme()
	{
		return view('admin.theme');
	} //<--- End method

	public function themeStore(Request $request)
	{
		$temp  = 'public/temp/'; // Temp
		$path  = 'public/img/'; // Path
		$pathAvatar = config('path.avatar');
		$pathCover = config('path.cover');
		$pathCategory = 'public/img-category/'; // Path Category

		$rules = array(
			'logo'   => 'mimes:png',
			'favicon'   => 'mimes:png',
			'index_image_top'   => 'mimes:jpg,jpeg',
			'index_image_bottom'   => 'mimes:jpg,jpeg',
		);

		$this->validate($request, $rules);

		set_time_limit(0);
		ini_set('memory_limit', '512M');

		//========== LOGO
		if ($request->hasFile('logo')) {

			$extension = $request->file('logo')->getClientOriginalExtension();
			$file      = 'logo-' . time() . '.' . $extension;

			if ($request->file('logo')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->logo);
			} // End File

			$this->settings->logo = $file;
			$this->settings->save();
		} // HasFile

		//======== FAVICON
		if ($request->hasFile('favicon')) {

			$extension  = $request->file('favicon')->getClientOriginalExtension();
			$file       = 'favicon-' . time() . '.' . $extension;

			if ($request->file('favicon')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->favicon);
			} // End File

			$this->settings->favicon = $file;
			$this->settings->save();
		} // HasFile

		//======== index_image_top
		if ($request->hasFile('index_image_top')) {

			$extension  = $request->file('index_image_top')->getClientOriginalExtension();
			$file       = 'header_index-' . time() . '.' . $extension;

			if ($request->file('index_image_top')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->image_header);
			} // End File

			$this->settings->image_header = $file;
			$this->settings->save();
		} // HasFile

		//======== index_image_bottom
		if ($request->hasFile('index_image_bottom')) {

			$extension  = $request->file('index_image_bottom')->getClientOriginalExtension();
			$file       = 'cover-' . time() . '.' . $extension;

			if ($request->file('index_image_bottom')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->image_bottom);
			} // End File

			$this->settings->image_bottom = $file;
			$this->settings->save();
		} // HasFile

		//======== Watermark
		if ($request->hasFile('watermark')) {

			$extension  = $request->file('watermark')->getClientOriginalExtension();
			$file       = 'watermark-' . time() . '.' . $extension;

			if ($request->file('watermark')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->watermark);
			} // End File

			$this->settings->watermark = $file;
			$this->settings->save();
		} // HasFile

		//======== header_colors
		if ($request->hasFile('header_colors')) {

			$extension  = $request->file('header_colors')->getClientOriginalExtension();
			$file       = 'header_colors-' . time() . '.' . $extension;

			if ($request->file('header_colors')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->header_colors);
			} // End File

			$this->settings->header_colors = $file;
			$this->settings->save();
		} // HasFile

		//======== header_cameras
		if ($request->hasFile('header_cameras')) {

			$extension  = $request->file('header_cameras')->getClientOriginalExtension();
			$file       = 'header_cameras-' . time() . '.' . $extension;

			if ($request->file('header_cameras')->move($temp, $file)) {
				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->header_cameras);
			} // End File

			$this->settings->header_cameras = $file;
			$this->settings->save();
		} // HasFile

		//======== avatar
		if ($request->hasFile('avatar')) {

			$extension  = $request->file('avatar')->getClientOriginalExtension();
			$file       = 'default-' . time() . '.' . $extension;

			$imgAvatar  = Image::make($request->file('avatar'))->fit(180, 180, function ($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
			})->encode($extension);

			// Copy folder
			Storage::put($pathAvatar . $file, $imgAvatar, 'public');

			// Update Avatar all users
			User::where('avatar', $this->settings->avatar)->update([
				'avatar' => $file
			]);

			// Delete old Avatar
			Storage::delete(config('path.avatar') . $this->settings->avatar);

			$this->settings->avatar = $file;
			$this->settings->save();
		} // HasFile

		//======== cover
		if ($request->hasFile('cover')) {

			$extension  = $request->file('cover')->getClientOriginalExtension();
			$file       = 'cover-' . time() . '.' . $extension;

			// Copy folder
			$request->file('cover')->storePubliclyAs($pathCover, $file);

			// Update Avatar all users
			User::where('cover', $this->settings->cover)->update([
				'cover' => $file
			]);

			// Delete old Avatar
			Storage::delete(config('path.cover') . $this->settings->cover);

			$this->settings->cover = $file;
			$this->settings->save();
		} // HasFile

		//======== img_category
		if ($request->hasFile('img_category')) {

			$extension  = $request->file('img_category')->getClientOriginalExtension();
			$file       = 'default-' . time() . '.' . $extension;

			if ($request->file('img_category')->move($temp, $file)) {

				$image = Image::make($temp . $file);

				$image->fit(400, 400)->save($temp . $file);

				\File::copy($temp . $file, $pathCategory . $file);
				\File::delete($temp . $file);
				\File::delete($pathCategory . $this->settings->img_category);
			} // End File

			$this->settings->img_category = $file;
			$this->settings->save();
		} // HasFile

		//======== img_collection
		if ($request->hasFile('img_collection')) {

			$extension  = $request->file('img_collection')->getClientOriginalExtension();
			$file       = 'img-collection-' . time() . '.' . $extension;

			if ($request->file('img_collection')->move($temp, $file)) {

				$image = Image::make($temp . $file);

				$image->fit(280, 160)->save($temp . $file);

				\File::copy($temp . $file, $path . $file);
				\File::delete($temp . $file);
				\File::delete($path . $this->settings->img_collection);
			} // End File

			$this->settings->img_collection = $file;
			$this->settings->save();
		} // HasFile

		//======= CLEAN CACHE
		\Artisan::call('cache:clear');

		return redirect('panel/admin/theme')
			->with('success_message', trans('misc.success_update'));
	} //<--- End method

	public function payments()
	{
		return view('admin.payments-settings');
	} //<--- End Method

	public function savePayments(Request $request)
	{

		$sql = AdminSettings::first();

		$rules = [
			'currency_code' => 'required|alpha',
			'currency_symbol' => 'required',
		];

		$this->validate($request, $rules);

		$sql->currency_symbol  = $request->currency_symbol;
		$sql->currency_code    = strtoupper($request->currency_code);
		$sql->currency_position    = $request->currency_position;
		$sql->min_sale_amount   = $request->min_sale_amount;
		$sql->max_sale_amount   = $request->max_sale_amount;
		$sql->min_deposits_amount   = $request->min_deposits_amount;
		$sql->max_deposits_amount   = $request->max_deposits_amount;
		$sql->fee_commission        = $request->fee_commission;
		$sql->fee_commission_non_exclusive = $request->fee_commission_non_exclusive;
		$sql->amount_min_withdrawal    = $request->amount_min_withdrawal;
		$sql->decimal_format = $request->decimal_format;

		$sql->save();

		\Session::flash('success_message', trans('admin.success_update'));

		return redirect('panel/admin/payments');
	} //<--- End Method

	public function purchases()
	{

		$data = Purchases::orderBy('id', 'desc')->paginate(30);

		return view('admin.purchases')->withData($data);
	} //<--- End Method

	public function deposits()
	{

		$data = Deposits::orderBy('id', 'desc')->paginate(30);

		return view('admin.deposits')->withData($data);
	} //<--- End Method

	public function withdrawals()
	{

		$data = Withdrawals::orderBy('id', 'DESC')->paginate(50);
		return view('admin.withdrawals', ['data' => $data, 'settings' => $this->settings]);
	} //<--- End Method

	public function withdrawalsView($id)
	{
		$data = Withdrawals::findOrFail($id);
		return view('admin.withdrawal-view', ['data' => $data, 'settings' => $this->settings]);
	} //<--- End Method

	public function withdrawalsPaid(Request $request)
	{

		$data = Withdrawals::findOrFail($request->id);

		// Set Withdrawal as Paid
		$data->status    = 'paid';
		$data->date_paid = \Carbon\Carbon::now();
		$data->save();

		$user = $data->user();

		// Set Balance a zero
		$user->balance = 0;
		$user->save();

		//<------ Send Email to User ---------->>>
		$amount       = Helper::amountFormatDecimal($data->amount) . ' ' . $this->settings->currency_code;
		$sender       = $this->settings->email_no_reply;
		$titleSite    = $this->settings->title;
		$fullNameUser = $user->name ? $user->name : $user->username;
		$_emailUser   = $user->email;

		Mail::send(
			'emails.withdrawal-processed',
			array(
				'amount'     => $amount,
				'fullname'   => $fullNameUser
			),
			function ($message) use ($sender, $fullNameUser, $titleSite, $_emailUser) {
				$message->from($sender, $titleSite)
					->to($_emailUser, $fullNameUser)
					->subject(trans('misc.withdrawal_processed') . ' - ' . $titleSite);
			}
		);
		//<------ Send Email to User ---------->>>

		return redirect('panel/admin/withdrawals');
	} //<--- End Method

	public function paymentsGateways($id)
	{

		$data = PaymentGateways::findOrFail($id);
		$name = ucfirst($data->name);

		return view('admin.' . str_slug($name) . '-settings')->withData($data);
	} //<--- End Method

	public function savePaymentsGateways($id, Request $request)
	{

		$data = PaymentGateways::findOrFail($id);

		$input = $_POST;

		$this->validate($request, [
			'email'    => 'email',
		]);

		$data->fill($input)->save();

		\Session::flash('success_message', trans('admin.success_update'));

		return back();
	} //<--- End Method

}
