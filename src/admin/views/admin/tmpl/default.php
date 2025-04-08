<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<form action="index.php?option=com_memberportal&task=uploadExcel" method="post" enctype="multipart/form-data" id="adminForm" name="adminForm">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th width="100%">Upload File</th>
        </tr>
        </thead>
        <tfoot>
            <tr>
                <td>
                    <input type="submit"></input>
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
                            <input type="checkbox" name="dry_run" value="1"> Dry Run Mode (no database changes will be made)
                        </label>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</form>