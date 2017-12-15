<?php
namespace app\www\controller;

/**
 * Class Upload
 * @package app\admin\controller
 * ajax异步上传文件控制器
 */
class Upload extends Base{

    public function upload_file(){
        $filename = request()->param('id');
        $folder = request()->param('folder');
        $uploadresult = $this->upload( ROOT_PATH.'public/uploads/'.$folder, $filename);


        if (!is_array($uploadresult)){

            $url = '/uploads/'.$folder.'/'.$uploadresult;
            echo json_encode($url);
        }else{
            foreach ($uploadresult as $value) {
                $result[] = '/uploads/' . $folder.'/'.$value;
            }
            echo json_encode($result);
        }
    }

}