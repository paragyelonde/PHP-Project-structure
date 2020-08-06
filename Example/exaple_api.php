<?php
/**
 * Created by PhpStorm.
 * User: parag
 * Date: 23/9/19
 * Time: 11:29 AM
 *
 */

include '../config/MySQLManager.php';
$manager = new MySQLManager();

$data = json_decode(file_get_contents('php://input'), true);

if ($_GET['page'] == 'get_all_info') {
    get_all_infos($manager);
}
if ($_GET['page'] == 'get_single_info') {
    $info_id = $_GET['info_id'];
    get_single_info($manager, $info_id);
}
if ($data['page'] == 'add_new_info') {
    add_new_info($manager, $data);
}
if ($data['page'] == 'update_info') {
    update_info($manager, $data);
}
if ($data['page'] == 'restore_info') {
    restore_info($manager, $data);
}
if ($data['page'] == 'delete_info') {
    delete_info($manager, $data);
}
if ($_POST['page'] == 'upload_info_image') {
    upload_info_image($manager, $_POST, $_FILES);
}
if ($_POST['page'] == 'send_mail') {
    send_mail($manager, $data);
}


// get all infos
function get_all_infos($manager)
{
    try {
        $result = $manager->select("info_master", [
            "is_active" => 1
        ]);

        echo json_encode(array(['res_code' => 1, 'data' => $result]));
    } catch (Exception $e) {
    }
}

// get single info
function get_single_info($manager, $info_id)
{
    try {
        $result = $manager->select("info_master", [
            "is_active" => 1
        ]);

        $result[0]['images'] = get_info_related_images($manager, $info_id);

        echo json_encode(array(['res_code' => 1, 'data' => $result[0]]));
    } catch (Exception $e) {
    }
}

//add_new_info
function add_new_info($manager, $data)
{
    try {
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $email = $data['email'];
        $password = $data['password'];
        $role = $data['role'];
        $is_active = 1;

        $result = $manager->insert("info_master", [
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "password" => $password,
            "role" => $role,
            "is_active" => $is_active,
            "created_at" => date('Y-m-d H:i:s'),
            "modified_at" => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            $res = array(['res_code' => 1, 'data' => $result]);
        } else {
            $res = array(['res_code' => 0]);
        }
        echo json_encode($res);
    } catch (Exception $e) {
    }
}

//update_info
function update_info($manager, $data)
{
    try {
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $email = $data['email'];
        $password = $data['password'];
        $role = $data['role'];

        $result = $manager->update("info_master", [
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "password" => $password,
            "role" => $role,
            "modified_at" => date('Y-m-d H:i:s')
        ], [
            "id" => $data['info_id']
        ]);

        if ($result) {
            $res = array(['res_code' => 1, 'data' => $result]);
        } else {
            $res = array(['res_code' => 0]);
        }
        echo json_encode($res);
    } catch (Exception $e) {
    }
}

//restore_info
function restore_info($manager, $data)
{
    try {
        $result = $manager->update("info_master", [
            "is_active" => 1,
        ], [
            "id" => $data['info_id']
        ]);

        if ($result) {
            $res = array(['res_code' => 1, 'data' => $result]);
        } else {
            $res = array(['res_code' => 0]);
        }
        echo json_encode($res);
    } catch (Exception $e) {
    }
}

//delete_info
function delete_info($manager, $data)
{
    try {
        $result = $manager->update("info_master", [
            "is_active" => 0,
        ], [
            "id" => $data['info_id']
        ]);

        if ($result) {
            $res = array(['res_code' => 1, 'data' => $result]);
        } else {
            $res = array(['res_code' => 0]);
        }
        echo json_encode($res);
    } catch (Exception $e) {
    }
}

//upload image
function upload_info_image($manager, $info_data, $files)
{
    try {

        $result = $manager->upload_file($files['image'], "../uploads/images/infos/"); //parameters image file and location where to save image file.

        if ($result[0]['res_code']) {
            echo json_encode($info_data);
            $result = $manager->update("info_master", [
                "image_url" => $result[0]['file_name'],
                "modified_at" => date('Y-m-d H:i:s')
            ], [
                "id" => $info_data['info_id']
            ]);
            if ($result) {
                $res = array(['res_code' => 1, 'data' => $result]);
            } else {
                $res = array(['res_code' => 0]);
            }

        } else {
            $res = array(['res_code' => 0]);
        }
        echo json_encode($res);

    } catch (Exception $e) {
    }
}

//get info related images
function get_info_related_images($manager, $info_id)
{
    try {
        $result = $manager->select("info_images", [
            "info_id" => $info_id,
        ]);

        return $result;
    } catch (Exception $e) {
    }
}


function send_mail($manager, $data)
{
    $isMailSend = $manager->send_mail($data['email_to'], $data['subject'], $data['message']);
    echo json_encode($isMailSend);
}
