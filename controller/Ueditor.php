<?php
namespace app\www\controller;
use think\Controller;
use think\Image;


class Ueditor extends Controller
{
    public function index()
    {

        //header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
        //header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");
        $CONFIG =  array(
					    'imageActionName' => 'uploadimage',
					    'imageFieldName' => 'upfile',
					    'imageMaxSize' => 2048000,
					    'imageAllowFiles' => array(
					            '.png','.jpg','.jpeg','.gif','.bmp'
					    ),
					    'imageCompressEnable' => 1,
					    'imageCompressBorder' => 1600,
					    'imageInsertAlign' => 'none',
					    'imageUrlPrefix' => '',
					    'imagePathFormat' => '/ad_upimg/{yyyy}{mm}{dd}/{time}{rand:6}',
					    'scrawlActionName' => 'uploadscrawl',
					    'scrawlFieldName' => 'upfile',
					    'scrawlPathFormat' => '/ueditor/php/upload/images/{yyyy}{mm}{dd}/{time}{rand:6}',
					    'scrawlMaxSize' => 2048000,
					    'scrawlUrlPrefix' =>'',
					    'scrawlInsertAlign' => 'none',
					    'snapscreenActionName' => 'uploadimage',
					    'snapscreenPathFormat' =>'/ueditor/php/upload/images/{yyyy}{mm}{dd}/{time}{rand:6}',
					    'snapscreenUrlPrefix' => '',
					    'snapscreenInsertAlign' => 'none',
					    'catcherLocalDomain' => array(
					            '0' => '127.0.0.1',
					            '1' => 'localhost',
					            '2' => 'img.baidu.com',
					    ),
					    'catcherActionName' => 'catchimage',
					    'catcherFieldName' => 'source',
					    'catcherPathFormat' =>'/ueditor/php/upload/images/{yyyy}{mm}{dd}/{time}{rand:6}',
					    'catcherUrlPrefix' =>'',
					    'catcherMaxSize' => '2048000',
					    'catcherAllowFiles' => array(
					            '.png','.jpg','.jpeg','.gif','.bmp'
					    ),
					    'videoActionName' => 'uploadvideo',
					    'videoFieldName' => 'upfile',
					    'videoPathFormat' => '/ueditor/php/upload/video/{yyyy}{mm}{dd}/{time}{rand:6}',
					    'videoUrlPrefix' =>'',
					    'videoMaxSize' => 102400000,
					    'videoAllowFiles' => array(
					           '.flv','.swf','.mkv','.avi','.rm','.rmvb','.mpeg','.mpg','.ogg','.ogv','.mov','.wmv','.mp4','.webm','.mp3','.wav','.mid'
					    ),
					    'fileActionName' => 'uploadfile',
					    'fileFieldName' => 'upfile',
					    'filePathFormat' => '/ueditor/php/upload/file/{yyyy}{mm}{dd}/{time}{rand:6}',
					    'fileUrlPrefix' => '',
					    'fileMaxSize' => 51200000,
					    'fileAllowFiles' =>array(
					            '.png','.jpg','.jpeg','.gif','.bmp','.flv','.swf','.mkv','.avi','.rm','.rmvb','.mpeg','.mpg','.ogg','.ogv','.mov','.wmv','.mp4','.webm','.mp3','.wav','.mid','.rar','.zip','.tar','.gz','.7z','.bz2','.cab','.iso','.doc','.docx','.xls','.xlsx','.ppt','.pptx','.pdf','.txt','.md','.xml'
					    ),

					    'imageManagerActionName' => 'listimage',
					    'imageManagerListPath' => '/ueditor/php/upload/images/',
					    'imageManagerListSize' => '20',
					    'imageManagerUrlPrefix' => '',
					    'imageManagerInsertAlign' => 'none',
					    'imageManagerAllowFiles' => array(
					            '.png','.jpg','.jpeg','.gif','.bmp'
					     ),
					    'fileManagerActionName' => 'listfile',
					    'fileManagerListPath' => '/ueditor/php/upload/file/',
					    'fileManagerUrlPrefix' =>'',
					    'fileManagerListSize' => 20,
					    'fileManagerAllowFiles' => array(
					           '.png','.jpg','.jpeg','.gif','.bmp','.flv','.swf','.mkv','.avi','.rm','.rmvb','.mpeg','.mpg','.ogg','.ogv','.mov','.wmv','.mp4','.webm','.mp3','.wav','.mid','.rar',       '.zip','.tar','.gz','.7z','.bz2','.cab','.iso','.doc','.docx','.xls','.xlsx','.ppt','.pptx','.pdf','.txt','.md','.xml'
					        )
						);
        //json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(request()->domain()."/lib/ueditor/1.4.3/php/config.json")), true);
        // $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("config.json")), true);
        $action = $_GET['action'];
        switch ($action) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
        	   $fieldName = $CONFIG['imageFieldName'];
		       $result = $this->upFile($fieldName);
		       break;
            /* 上传涂鸦 */
            case 'uploadscrawl':
            /* 上传视频 */
            case 'uploadvideo':
            /* 上传文件 */
            case 'uploadfile':
                $result = include(dirname(__FILE__)."/action_upload.php");
                break;

            /* 列出图片 */
            case 'listimage':
                $result = include(dirname(__FILE__)."/action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $result = include(dirname(__FILE__)."/action_list.php");
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = include(dirname(__FILE__)."/action_crawler.php");
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }
        if($_GET["callback"]){
             var_dump($_GET["callback"]);die;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }
    //上传文件
	private function upFile($fieldName){
		$file = request()->file($fieldName);
		$info = $file->move(ROOT_PATH.'public/upload/admin/img');
		if($info){//上传成功
			$fname=request()->domain().'/upload/admin/img/'.str_replace('\\','/',$info->getSaveName());
			$imgArr = explode(',', 'jpg,gif,png,jpeg,bmp,ttf,tif');
			$imgExt= strtolower($info->getExtension());
			$isImg = in_array($imgExt,$imgArr);

			/*if($isImg){//如果是图片，开始处理
				$images = Image::open($fname);
				$thumbnail = 1;
				$water = 1;

				if($thumbnail == 1){//生成缩略图
					$images->thumb(500,500,1)->save('.'.$fname);
				}
			}*/

			$data=array(
				'state' => 'SUCCESS',
				'url' => $fname,
				'title' => $info->getFilename(),
				'original' => $info->getFilename(),
				'type' => '.' . $info->getExtension(),
				'size' => $info->getSize(),
			);
		}else{
			$data=array(
			    'state' => $info->getError(),
			);
		}
		return json_encode($data);
	}

    //列出图片
	private function fileList($allowFiles,$listSize,$get){
		$dirname = './public/uploads/';
		$allowFiles = substr(str_replace(".","|",join("",$allowFiles)),1);

		/* 获取参数 */
		$size = isset($get['size']) ? htmlspecialchars($get['size']) : $listSize;
		$start = isset($get['start']) ? htmlspecialchars($get['start']) : 0;
		$end = $start + $size;

		/* 获取文件列表 */
		$path = $dirname;
		$files = $this->getFiles($path,$allowFiles);
		if(!count($files)){
		    return json_encode(array(
		        "state" => "no match file",
		        "list" => array(),
		        "start" => $start,
		        "total" => count($files)
		    ));
		}

		/* 获取指定范围的列表 */
		$len = count($files);
		for($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		    $list[] = $files[$i];
		}

		/* 返回数据 */
		$result = json_encode(array(
		    "state" => "SUCCESS",
		    "list" => $list,
		    "start" => $start,
		    "total" => count($files)
		));

		return $result;
	}

   	/*
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	*/
    private function getFiles($path,$allowFiles,&$files = array()){
	    if(!is_dir($path)) return null;
	    if(substr($path,strlen($path)-1) != '/') $path .= '/';
	    $handle = opendir($path);

	    while(false !== ($file = readdir($handle))){
	        if($file != '.' && $file != '..'){
	            $path2 = $path.$file;
	            if(is_dir($path2)){
	                $this->getFiles($path2,$allowFiles,$files);
	            }else{
		            if(preg_match("/\.(".$allowFiles.")$/i",$file)){
		                $files[] = array(
		                    'url' => substr($path2,1),
		                    'mtime' => filemtime($path2)
		                );
		            }
	            }
	        }
	    }

	    return $files;
    }

    //抓取远程图片
	private function saveRemote($config,$fieldName){
	    $imgUrl = htmlspecialchars($fieldName);
	    $imgUrl = str_replace("&amp;","&",$imgUrl);

	    //http开头验证
	    if(strpos($imgUrl,"http") !== 0){
	        $data=array(
		        'state' => '链接不是http链接',
		    );
	        return json_encode($data);
	    }
	    //获取请求头并检测死链
	    $heads = get_headers($imgUrl);
	    if(!(stristr($heads[0],"200") && stristr($heads[0],"OK"))){
	        $data=array(
		        'state' => '链接不可用',
		    );
	        return json_encode($data);
	    }
	    //格式验证(扩展名验证和Content-Type验证)
	    $fileType = strtolower(strrchr($imgUrl,'.'));
	    if(!in_array($fileType,$config['allowFiles']) || stristr($heads['Content-Type'],"images")){
	        $data=array(
		        'state' => '链接contentType不正确',
		    );
	        return json_encode($data);
	    }

	    //打开输出缓冲区并获取远程图片
	    ob_start();
	    $context = stream_context_create(
	        array('http' => array(
	            'follow_location' => false // don't follow redirects
	        ))
	    );
	    readfile($imgUrl,false,$context);
	    $img = ob_get_contents();
	    ob_end_clean();
	    preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/",$imgUrl,$m);

	    $dirname = './public/uploads/remote/';
	    $file['oriName'] = $m ? $m[1] : "";
	    $file['filesize'] = strlen($img);
	    $file['ext'] = strtolower(strrchr($config['oriName'],'.'));
	    $file['name'] = uniqid().$file['ext'];
	    $file['fullName'] = $dirname.$file['name'];
	    $fullName = $file['fullName'];

	    //检查文件大小是否超出限制
	    if($file['filesize'] >= ($config["maxSize"])){
  		    $data=array(
			    'state' => '文件大小超出网站限制',
		    );
		    return json_encode($data);
	    }

	    //创建目录失败
	    if(!file_exists($dirname) && !mkdir($dirname,0777,true)){
  		    $data=array(
			    'state' => '目录创建失败',
		    );
		    return json_encode($data);
	    }else if(!is_writeable($dirname)){
  		    $data=array(
			    'state' => '目录没有写权限',
		    );
		    return json_encode($data);
	    }

	    //移动文件
	    if(!(file_put_contents($fullName, $img) && file_exists($fullName))){ //移动失败
  		    $data=array(
			    'state' => '写入文件内容错误',
		    );
		    return json_encode($data);
	    }else{ //移动成功
	        $data=array(
			    'state' => 'SUCCESS',
			    'url' => substr($file['fullName'],1),
			    'title' => $file['name'],
			    'original' => $file['oriName'],
			    'type' => $file['ext'],
			    'size' => $file['filesize'],
		    );
	    }

	    return json_encode($data);
	}

    /*
	 * 处理base64编码的图片上传
	 * 例如：涂鸦图片上传
	*/
	private function upBase64($config,$fieldName){
	    $base64Data = $_POST[$fieldName];
	    $img = base64_decode($base64Data);

	    $dirname = './public/uploads/scrawl/';
	    $file['filesize'] = strlen($img);
	    $file['oriName'] = $config['oriName'];
	    $file['ext'] = strtolower(strrchr($config['oriName'],'.'));
	    $file['name'] = uniqid().$file['ext'];
	    $file['fullName'] = $dirname.$file['name'];
	    $fullName = $file['fullName'];

 	    //检查文件大小是否超出限制
	    if($file['filesize'] >= ($config["maxSize"])){
  		    $data=array(
			    'state' => '文件大小超出网站限制',
		    );
		    return json_encode($data);
	    }

	    //创建目录失败
	    if(!file_exists($dirname) && !mkdir($dirname,0777,true)){
	        $data=array(
			    'state' => '目录创建失败',
		    );
		    return json_encode($data);
	    }else if(!is_writeable($dirname)){
	        $data=array(
			    'state' => '目录没有写权限',
		    );
		    return json_encode($data);
	    }

	    //移动文件
	    if(!(file_put_contents($fullName, $img) && file_exists($fullName))){ //移动失败
            $data=array(
		        'state' => '写入文件内容错误',
		    );
	    }else{ //移动成功
	        $data=array(
			    'state' => 'SUCCESS',
			    'url' => substr($file['fullName'],1),
			    'title' => $file['name'],
			    'original' => $file['oriName'],
			    'type' => $file['ext'],
			    'size' => $file['filesize'],
		    );
	    }

	    return json_encode($data);
	}
}

