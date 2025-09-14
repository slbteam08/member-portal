<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

?>
<form action="index.php?option=com_memberportal&task=uploadOfferings" method="post" enctype="multipart/form-data" id="adminForm" name="adminForm">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th width="100%"><h4>上傳檔案</h4></th>
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

<div class="container-fluid" style="margin-top: 50px;">
    <div class="row">
        <div class="col-md-12">
            <!-- File Requirements Section -->
            <div class="panel panel-info">
                <div class="panel-body">
                    <h4>檔案格式要求：</h4>
                    <ul>
                        <li><strong>檔案類型：</strong> .xlsx 或 .xls 檔案</li>
                        <li><strong>工作表名稱：</strong>沒有限制，但必須是第一個工作表</li>
                        <li><strong>編碼格式：</strong> UTF-8</li>
                    </ul>
                    
                    <h4>工作表欄位要求：</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>欄位順序</th>
                                    <th>欄位名稱</th>
                                    <th>格式要求</th>
                                    <th>說明</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>崇拜編碼</td>
                                    <td>文字</td>
                                    <td>崇拜編碼</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>會員名稱</td>
                                    <td>文字</td>
                                    <td>會員名稱（暫不使用）</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>奉獻日期</td>
                                    <td>YYYY-MM-DD 或 YYYY-M-D</td>
                                    <td>奉獻日期（例如：2024-01-15 或 2024-1-15）</td>
                                </tr>                                
                                <tr>
                                    <td>4</td>
                                    <td>付款方式</td>
                                    <td>文字</td>
                                    <td>付款方式（例如：現金、支票）</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>支票</td>
                                    <td>文字</td>
                                    <td>支票號碼（如果付款方式為支票）</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>收據類別</td>
                                    <td>文字</td>
                                    <td>"全年"或"獨立"</td>
                                </tr>
                                <tr>
                                    <td>7 - 13</td>
                                    <td>奉獻金額</td>
                                    <td>數字</td>
                                    <td>以下項目的奉獻金額（順序）：<br>十一奉獻、感恩奉獻、經常奉獻、建堂基金、福音事工、愛鄰舍基金、特別奉獻</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
