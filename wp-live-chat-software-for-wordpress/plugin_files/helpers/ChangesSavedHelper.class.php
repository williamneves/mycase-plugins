<?php

require_once('LiveChatHelper.class.php');

class ChangesSavedHelper extends LiveChatHelper
{
	public function render()
	{
		if (LiveChat::get_instance()->changes_saved())
		{
			return '<div id="changes_saved_info" class="updated installed_ok"><p>Advanced settings saved successfully.</p></div>';
		}

		return '';
	}
}