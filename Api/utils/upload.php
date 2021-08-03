<?php
// 允许上传的图片后缀

include "./msg.php";

$allowedExts = array("gif", "jpeg", "jpg", "png");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);     // 获取文件后缀名
if ((($_FILES["file"]["type"] == "image/gif")
        || ($_FILES["file"]["type"] == "image/jpeg")
        || ($_FILES["file"]["type"] == "image/jpg")
        || ($_FILES["file"]["type"] == "image/jpeg")
        || ($_FILES["file"]["type"] == "image/x-png")
        || ($_FILES["file"]["type"] == "image/png"))
    && in_array($extension, $allowedExts))
{
    if ($_FILES["file"]["error"] > 0)
    {
        $msg = new Core\msg(200, "错误：: " . $_FILES["file"]["error"]);
        echo json_encode($msg->getJson());
    }
    else
    {
//        echo "上传文件名: " . $_FILES["file"]["name"] . "<br>";
//        echo "文件类型: " . $_FILES["file"]["type"] . "<br>";
//        echo "文件大小: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
//        echo "文件临时存储的位置: " . $_FILES["file"]["tmp_name"] . "<br>";

        // 判断当前目录下的 upload 目录是否存在该文件
        // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
        if (file_exists("/usr/local/nginx/html/Shop/Web/pay/uploads/" . $_FILES["file"]["name"]))
        {
            $url= "http://10.0.0.100/Shop/Web/pay/uploads/" . $_FILES["file"]["name"];
            $msg = new Core\msg(0, $_FILES["file"]["name"] . " 文件已经存在。 ",$url);
            echo json_encode($msg->getJson());
        }
        else
        {
            // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
            move_uploaded_file($_FILES["file"]["tmp_name"], "/usr/local/nginx/html/Shop/Web/pay/uploads/" . $_FILES["file"]["name"]);
            $url= "http://10.0.0.100/Shop/Web/pay/uploads/" . $_FILES["file"]["name"];
            $msg = new Core\msg(0, "上传成功: " ._FILES["file"]["name"],$url);
            echo json_encode($msg->getJson());

        }
    }
}
else
{
    $msg = new Core\msg(500, "非法的文件格式");
    echo json_encode($msg->getJson());
}
