<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<form action="index.php?option=com_memberportal&task=uploadExcel" method="post" enctype="multipart/form-data" id="adminForm" name="adminForm">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th width="100%">上傳檔案</th>
        </tr>
        </thead>
        <tfoot>
            <tr>
                <td>
                    <input type="submit" value="上傳"></input>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <tr>
                <td>
                    <input type="file" name="upload_file"></input>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="dry_run" value="1"> 測試模式（不會對資料庫進行任何更改）
                        </label>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</form>

<a href="index.php?option=com_memberportal&task=downloadMemberStatusExcel" class="btn btn-primary" style="margin: 8px">下載會員狀態 Excel</a>