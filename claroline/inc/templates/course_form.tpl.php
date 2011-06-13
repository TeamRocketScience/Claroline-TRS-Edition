<!-- $Id: course_form.tpl.php 13032 2011-04-01 14:12:56Z abourguignon $ -->

<form method="post" class="msform" id="courseSettings" action="<?php echo $this->formAction; ?>">
    <?php echo $this->relayContext ?>
    <input type="hidden" name="cmd" value="<?php echo (empty($this->course->courseId)?'rqProgress':'exEdit'); ?>" />
    <input type="hidden" name="course_id" value="<?php echo (empty($this->course->id)?'':$this->course->id); ?>" />
    <input type="hidden" name="course_isSourceCourse" value="<?php echo (empty($this->course->isSourceCourse)?'':$this->course->isSourceCourse); ?>" />
    <input type="hidden" name="course_sourceCourseId" value="<?php echo (empty($this->course->sourceCourseId)?'':$this->course->sourceCourseId); ?>" />
    <input type="hidden" name="claroFormId" value="<?php echo uniqid(''); ?>" />
    
    <?php echo $this->course->getHtmlParamList('POST'); ?>
    
    <p>
        <a href="#" class="expand-all"><?php echo get_lang('Expand all'); ?></a> /
        <a href="#" class="collapse-all"><?php echo get_lang('Collapse all'); ?></a>
    </p>
    
    <!-- FIRST SECTION: basic settings -->
    <fieldset  class="collapsible" id="mandatories">
        <legend>
            <a href="#" class="doCollapse"><?php echo get_lang('Basic settings'); ?></a>
        </legend>
        <div class="collapsible-wrapper">
            <dl>
                
                <!-- Course title -->
                <dt>
                    <label for="course_title">
                        <?php echo get_lang('Course title'); ?>
                    </label>
                    <?php if (get_conf('human_label_needed')) : ?>
                    <span class="required">*</span>
                    <?php endif; ?>
                </dt>
                <dd>
                    <input type="text" name="course_title" id="course_title" value="<?php echo htmlspecialchars($this->course->title); ?>" size="60" />
                    <?php if (empty($this->course->courseId)) : ?>
                    <br />
                    <span class="notice"><?php echo get_lang('e.g. <em>History of Literature</em>'); ?></span>
                    <?php endif; ?>
                </dd>
                
                <!-- Course code -->
                <dt>
                    <label for="course_officialCode">
                        <?php echo get_lang('Course code'); ?>
                    </label><span class="required">*</span>
                </dt>
                <dd>
                    <input type="text" id="course_officialCode" name="course_officialCode" value="<?php echo htmlspecialchars($this->course->officialCode); ?>" size="20" maxlength="12" />
                    <?php if (empty($this->course->courseId)) : ?>
                    <br />
                    <span class="notice"><?php echo get_lang('max. 12 characters, e.g. <em>ROM2121</em>'); ?></span>
                    <?php endif; ?>
                </dd>
                
                <!-- Course categories -->
                <?php if (empty($this->course->sourceCourseId)) : ?>
                <dt>
                    <label>
                        <?php echo get_lang('Categories'); ?>
                    </label>
                </dt>
                <dd>
                    <table class="multiselect">
                      <tr>
                        <td>
                            <label for="mslist1">
                                <?php echo get_lang('Linked categories'); ?>
                            </label>
                            <br />
                            <select multiple="multiple" name="linked_categories[]" id="mslist1" size="10">
                                <?php echo $this->linkedCategoriesListHtml; ?>
                            </select>
                        </td>
                        <td class="arrows">
                            <a href="#" class="msadd"><img src="<?php echo get_icon_url('go_right'); ?>" /></a>
                            <br /><br />
                            <a href="#" class="msremove"><img src="<?php echo get_icon_url('go_left'); ?>" /></a>
                        </td>
                        <td>
                            <label for="mslist2">
                                <?php echo get_lang('Unlinked categories'); ?>
                            </label>
                            <br />
                            <select multiple="multiple" name="unlinked_categories[]" id="mslist2" size="10">
                                <?php echo $this->unlinkedCategoriesListHtml; ?>
                            </select>
                        </td>
                      </tr>
                    </table>
                    <?php if (empty($this->course->courseId)) : ?>
                    <span class="notice">
                        <?php echo get_lang('Feel free not to associate courses to any categories.'); ?>
                    </span>
                    <?php endif; ?>
                </dd>
                <?php endif; ?>
                
                <!-- Course language select box -->
                <dt>
                    <label for="course_language">
                        <?php echo get_lang('Language'); ?>
                    </label><span class="required">*</span>
                </dt>
                <dd>
                    <?php echo claro_html_form_select('course_language', $this->languageList, $this->course->language, array('id'=>'course_language')); ?>
                </dd>
                
                <!-- Course titular -->
                <dt>
                    <label for="course_titular">
                        <?php echo get_lang('Lecturer(s)'); ?>
                    </label>
                </dt>
                <dd>
                    <input type="text"  id="course_titular" name="course_titular" value="<?php echo htmlspecialchars($this->course->titular); ?>" size="60" />
                </dd>
                
                <!-- Course titular's email -->
                <dt>
                    <label for="course_email">
                        <?php echo get_lang('Email'); ?>
                        <?php if (get_conf('course_email_needed')) : ?>
                        <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                </dt>
                <dd>
                    <input type="text" id="course_email" name="course_email" value="<?php echo htmlspecialchars($this->course->email); ?>" size="60" maxlength="255" />
                </dd>
                
                <!-- Course access -->
                <dt>
                    <?php echo get_lang('Course access'); ?><span class="required">*</span>
                </dt>
                <dd>
                    <img src="<?php echo get_icon_url('access_open'); ?>" alt="<?php echo get_lang('open'); ?>" />
                    <input type="radio"<?php echo $this->publicDisabled; ?> id="access_public" name="course_access" value="public" <?php echo ($this->course->access == 'public' ? 'checked="checked"':''); ?> />
                    &nbsp;
                    <label for="access_public"<?php echo $this->publicCssClass; ?>">
                        <?php echo get_lang('Access allowed to anybody (even without login)'); ?>
                    </label>
                    <?php echo $this->publicMessage; ?>
                    <br />
                    <img src="<?php echo get_icon_url('access_platform'); ?>" alt="<?php echo get_lang('open'); ?>" />
                    <input type="radio" id="access_reserved" name="course_access" value="platform" <?php echo ($this->course->access == 'platform' ? 'checked="checked"':''); ?> />
                    &nbsp;
                    <label for="access_reserved">
                        <?php echo get_lang('Access allowed only to platform members (user registered to the platform)'); ?>
                    </label>
                    <br />
                    <img src="<?php echo get_icon_url('access_locked'); ?>"  alt="<?php echo get_lang('locked'); ?>" />
                    <input type="radio" id="access_private" name="course_access" value="private" <?php echo ($this->course->access == 'private' ? 'checked="checked"':'' ); ?> />
                    &nbsp;
                    <label for="access_private">
                        <?php if (empty($this->course->courseId)) : ?>
                            <?php echo get_lang('Access allowed only to course members (people on the course user list)'); ?>
                        <?php else : ?>
                            <?php echo get_lang('Access allowed only to course members (people on the <a href="%url">course user list</a>)' , array('%url'=> '../user/user.php')); ?>
                        <?php endif; ?>
                    </label>
                </dd>
                
                <!-- Course registration + registration key -->
                <dt>
                    <?php echo get_lang('Optional settings'); ?><span class="required">*</span>
                </dt>
                <dd>
                    <img src="<?php echo get_icon_url('enroll_allowed'); ?>"  alt="" />
                    <input type="radio" id="registration_true" name="course_registration" value="open"'
                    <?php echo ( !isset($this->course->registration) || $this->course->registration === 'open' || $this->course->registration == 'validation' ? ' checked="checked"' : '' ); ?> />
                    &nbsp;
                    <label for="registration_true">
                        <?php echo get_lang('Allowed'); ?>
                    </label>
                    
                    <blockquote>
                    <img src="<?php echo get_icon_url('tick'); ?>"  alt="" />
                    <input type="checkbox" id="registration_validation" name="registration_validation"<?php echo ( $this->course->registration === 'validation' ? ' checked="checked"' : '' ); ?> />
                    &nbsp;
                    <label for="registration_validation">
                        <?php echo get_lang('Allowed with validation'); ?>
                    </label>
                    <br />
                    <img src="<?php echo get_icon_url('enroll_key'); ?>"  alt="" />
                    <input type="checkbox" id="registration_key" name="registration_key"<?php echo ( !empty($this->course->registrationKey) ? 'checked="checked"' : ''); ?> />
                    &nbsp;
                    <label for="registration_key">
                        <?php echo get_lang('Allowed with enrolment key'); ?>
                    </label>
                    &nbsp;
                    <input type="text" id="registrationKey" name="course_registrationKey" value="<?php echo htmlspecialchars($this->course->registrationKey); ?>" />
                    </blockquote>
                    
                    <img src="<?php echo get_icon_url('enroll_forbidden'); ?>"  alt="" />
                    <input type="radio" id="registration_false"  name="course_registration" value="close"<?php echo ( $this->course->registration === 'close' ? ' checked="checked"' : '' ); ?> />
                    &nbsp;
                    <label for="registration_false">
                        <?php echo get_lang('Denied'); ?>
                    </label>
                </dd>
                
                <!-- Course settings tips -->
                <dt>
                    &nbsp;
                </dt>
                <dd>
                    <span class="notice"><?php echo get_block('blockCourseSettingsTip'); ?></span>
                </dd>
                
            </dl>
        </div>
    </fieldset>
    
    
    <!-- SECOND SECTION: optionnal settings -->
    <fieldset  class="collapsible collapsed" id="options">
        <legend>
            <a href="#" class="doCollapse"><?php echo get_lang('Optionnal settings'); ?></a>
        </legend>
        <div class="collapsible-wrapper">
            <dl>
                
                <!-- Course department name -->
                <dt>
                    <label for="course_departmentName">
                        <?php if (get_conf('extLinkNameNeeded')) : ?>
                        <span class="required">*</span>
                        <?php endif; ?>
                        <?php echo get_lang('Department'); ?>
                    </label>
                </dt>
                <dd>
                    <input type="text" name="course_departmentName" id="course_departmentName" value="<?php echo htmlspecialchars($this->course->departmentName); ?>" size="20" maxlength="30" />
                </dd>
                
                <!-- Course department url -->
                <dt>
                    <label for="course_extLinkUrl" >
                        <?php echo get_lang('Department URL'); ?>
                        <?php if (get_conf('extLinkUrlNeeded')) : ?>
                        <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                </dt>
                <dd>
                    <input type="text" name="course_extLinkUrl" id="course_extLinkUrl" value="<?php echo htmlspecialchars($this->course->extLinkUrl); ?>" size="60" maxlength="180" />
                </dd>
                
            </dl>
        </div>
    </fieldset>
    
    <!-- THIRD SECTION: advanced options -->
    <fieldset id="advancedInformation" class="collapsible collapsed">
        <legend>
            <a href="#" class="doCollapse"><?php echo get_lang('Advanced options'); ?></a>
        </legend>
        <div class="collapsible-wrapper">
            <dl>
                
                <!-- Visibility in category list -->
                <dt>
                    <?php echo get_lang('Course visibility'); ?>
                </dt>
                <dd>
                    <img src="<?php echo get_icon_url('visible'); ?>" alt="" />
                    <input type="radio" id="visibility_show" name="course_visibility" value="1" <?php echo ($this->course->visibility ? 'checked="checked"':''); ?> />&nbsp;
                    <label for="visibility_show">
                        <?php echo get_lang('The course is shown in the courses listing'); ?>
                    </label>
                    <br />
                    <img src="<?php echo get_icon_url('invisible'); ?>" alt="" />
                    <input type="radio" id="visibility_hidden" name="course_visibility" value="0" <?php echo (!$this->course->visibility ? 'checked="checked"':''); ?> />&nbsp;
                    <label for="visibility_hidden">
                        <?php echo get_lang('Visible only to people on the user list'); ?>
                    </label>
                </dd>
                
                <dt>
                    <?php echo get_lang('Maximum number of users'); ?>
                </dt>
                <dd>
                    <input type="text" name="course_userLimit" id="course_userLimit" value="<?php echo $this->course->userLimit; ?>" />
                    <br />
                    <span class="notice">
                        <?php echo get_lang('Leave this field empty or use 0 if you don\'t want to limit the number of users in this course'); ?>
                    </span>
                </dd>
                
            </dl>
            
            <?php if (claro_is_platform_admin()) : ?>
            <!-- Course status -->
            <dl>
                
                <dt>
                    <?php echo get_lang('Status'); ?>
                </dt>
                <dd class="adminControl">
                    <input type="radio" id="course_status_enable" name="course_status_selection" value="enable"<?php echo ($this->course->status == 'enable' ? ' checked="checked"':''); ?> />&nbsp;
                    <label for="course_status_enable">
                        <?php echo get_lang('Available'); ?>
                    </label>
                    <br /><br />
                    
                    <input type="radio" id="course_status_date" name="course_status_selection" value="date" <?php echo ($this->course->status == 'date' ? ' checked="checked"':''); ?> />&nbsp;
                    <label for="course_status_date">
                        <?php echo get_lang('Available'); ?>&nbsp;'<?php echo get_lang('from'); ?> (<?php echo get_lang('included'); ?>)
                    </label>
                    <?php echo claro_html_date_form('course_publicationDay', 'course_publicationMonth', 'course_publicationYear', $this->course->publicationDate, 'numeric'); ?>&nbsp;
                    <span class="notice"><?php echo get_lang('(d/m/y)'); ?></span>
                    
                    
                    <blockquote>
                        <input type="checkbox" id="useExpirationDate" name="useExpirationDate" value="true"<?php echo ($this->course->useExpirationDate ? ' checked="checked"':''); ?> />
                        <label for="useExpirationDate">
                            <?php echo get_lang('to'); ?> (<?php echo get_lang('included'); ?>)
                        </label>
                        <?php echo claro_html_date_form('course_expirationDay', 'course_expirationMonth', 'course_expirationYear', $this->course->expirationDate, 'numeric'); ?>&nbsp;
                        <span class="notice"><?php echo get_lang('(d/m/y)'); ?></span>
                    </blockquote>
                    
                    
                    <input type="radio" id="course_status_disabled" name="course_status_selection" value="disable" <?php echo ($this->course->status == 'pending' || $this->course->status == 'disable' || $this->course->status == 'trash' ? ' checked="checked"':''); ?> />&nbsp;
                    <label for="course_status_disabled">
                        <?php echo get_lang('Not available'); ?>
                    </label>
                    
                    
                    <blockquote>
                        <input type="radio" id="status_pending" name="course_status" value="pending"<?php echo ($this->course->status == 'pending' || $this->course->status == 'enable' || $this->course->status == 'date' ? ' checked="checked"':'' ); ?> />&nbsp;
                        <label for="status_pending">
                            <?php echo get_lang('Reactivable by course manager'); ?>
                        </label>
                        <br />
                        
                        <input type="radio" id="status_disable" name="course_status" value="disable"<?php echo ($this->course->status == 'disable' ? ' checked="checked"':''); ?> />&nbsp;
                        <label for="status_disable">
                            <?php echo get_lang('Reactivable by administrator'); ?>
                        </label>
                        <br />
                        
                        <input type="radio" id="status_trash" name="course_status" value="trash"<?php echo ($this->course->status == 'trash' ? 'checked="checked"':''); ?> />&nbsp;
                        <label for="status_trash">
                            <?php echo get_lang('Move to trash'); ?>
                        </label>
                    </blockquote>
                </dd>
                
            </dl>
            <?php endif; ?>
        </div>
    </fieldset>
    
    <dl>
        <dt>
            <input type="submit" name="changeProperties" value="<?php echo get_lang('Ok'); ?>" />&nbsp;
            <?php echo claro_html_button($this->cancelUrl, get_lang('Cancel')); ?>
        </dt>
        <dd></dd>
    </dl>
</form>

<p class="notice">
    <?php echo get_lang('<span class="required">*</span> denotes required field'); ?>
</p>