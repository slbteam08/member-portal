<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="icon-upload"></i> <?php echo JText::_('COM_MEMBERPORTAL_UPLOAD_OFFERINGS_TITLE'); ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <form action="index.php?option=com_memberportal&task=uploadOfferings" method="post" enctype="multipart/form-data" id="uploadForm" name="uploadForm" class="form-horizontal">
                        
                        <div class="alert alert-info">
                            <i class="icon-info"></i> <?php echo JText::_('COM_MEMBERPORTAL_UPLOAD_OFFERINGS_INFO'); ?>
                        </div>

                        <div class="form-group">
                            <label for="upload_file" class="col-sm-3 control-label">
                                <?php echo JText::_('COM_MEMBERPORTAL_UPLOAD_FILE_LABEL'); ?> *
                            </label>
                            <div class="col-sm-9">
                                <input type="file" name="upload_file" id="upload_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                <small class="help-block">
                                    <?php echo JText::_('COM_MEMBERPORTAL_UPLOAD_FILE_HELP'); ?>
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="member_code" class="col-sm-3 control-label">
                                <?php echo JText::_('COM_MEMBERPORTAL_MEMBER_CODE_LABEL'); ?>
                            </label>
                            <div class="col-sm-9">
                                <input type="text" name="member_code" id="member_code" class="form-control" placeholder="<?php echo JText::_('COM_MEMBERPORTAL_MEMBER_CODE_PLACEHOLDER'); ?>">
                                <small class="help-block">
                                    <?php echo JText::_('COM_MEMBERPORTAL_MEMBER_CODE_HELP'); ?>
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="dry_run" id="dry_run" value="1">
                                        <?php echo JText::_('COM_MEMBERPORTAL_DRY_RUN_LABEL'); ?>
                                    </label>
                                </div>
                                <small class="help-block">
                                    <?php echo JText::_('COM_MEMBERPORTAL_DRY_RUN_HELP'); ?>
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon-upload"></i> <?php echo JText::_('COM_MEMBERPORTAL_UPLOAD_BUTTON'); ?>
                                </button>
                                <a href="index.php?option=com_memberportal" class="btn btn-default">
                                    <i class="icon-cancel"></i> <?php echo JText::_('COM_MEMBERPORTAL_CANCEL_BUTTON'); ?>
                                </a>
                            </div>
                        </div>

                        <?php echo JHtml::_('form.token'); ?>
                    </form>
                </div>
            </div>

            <!-- Instructions Panel -->
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="icon-info"></i> <?php echo JText::_('COM_MEMBERPORTAL_INSTRUCTIONS_TITLE'); ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <h4><?php echo JText::_('COM_MEMBERPORTAL_FILE_FORMAT_TITLE'); ?></h4>
                    <p><?php echo JText::_('COM_MEMBERPORTAL_FILE_FORMAT_DESC'); ?></p>
                    
                    <h4><?php echo JText::_('COM_MEMBERPORTAL_REQUIRED_COLUMNS_TITLE'); ?></h4>
                    <ul>
                        <li><strong>Date:</strong> <?php echo JText::_('COM_MEMBERPORTAL_DATE_COLUMN_DESC'); ?></li>
                        <li><strong>Amount:</strong> <?php echo JText::_('COM_MEMBERPORTAL_AMOUNT_COLUMN_DESC'); ?></li>
                        <li><strong>Type:</strong> <?php echo JText::_('COM_MEMBERPORTAL_TYPE_COLUMN_DESC'); ?></li>
                    </ul>

                    <h4><?php echo JText::_('COM_MEMBERPORTAL_EXAMPLE_TITLE'); ?></h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2024-01-15</td>
                                    <td>100.00</td>
                                    <td>Tithe</td>
                                    <td>Sunday offering</td>
                                </tr>
                                <tr>
                                    <td>2024-01-22</td>
                                    <td>50.00</td>
                                    <td>Offering</td>
                                    <td>Special collection</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
Joomla.submitform = function(task, form) {
    if (document.formvalidator.isValid(document.id('uploadForm'))) {
        Joomla.submit(task);
    }
}
</script>
