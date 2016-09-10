(function($)
{
var LiveChat =
{
	init: function()
	{
		this.externalLinks();
		this.resetLink();
		this.toggleForms();
		this.alreadyHaveAccountForm();
		this.newLicenseForm();
		this.controlPanelIframe();
		this.fadeChangesSaved();
	},

	externalLinks: function()
	{
		$('a.help').attr('target', '_blank');
	},

	resetLink: function()
	{
		$('#reset_settings a').click(function()
		{
			return confirm('This will reset your LiveChat plugin settings. Continue?');
		})
	},

	toggleForms: function()
	{
		var toggleForms = function()
		{
			// display account details page if license number is already known
			if ($('#choice_account').length == 0 || $('#choice_account_1').is(':checked'))
			{
				$('#livechat_new_account').hide();
				$('#livechat_already_have').show();
				$('#livechat_login').focus();
			}
			else if ($('#choice_account_0').is(':checked'))
			{
				$('#livechat_already_have').hide();
				$('#livechat_new_account').show();

				if ($.trim($('#name').val()).length == 0)
				{
					$('#name').focus();
				}
				else
				{			
					$('#password').focus();
				}
			}
		};

		toggleForms();
		$('#choice_account input').click(toggleForms);
	},

	alreadyHaveAccountForm: function()
	{
		$('#livechat_already_have form').submit(function()
		{
			if (parseInt($('#license_number').val()) == 0)
			{
				var login = $.trim($('#livechat_login').val());
				if (!login.length)
				{
					$('#livechat_login').focus();
					return false;
				}

				$('#livechat_already_have .ajax_message').removeClass('message').addClass('wait').html('Please wait&hellip;');
				
				$.ajax({
					url: 'https://api.livechatinc.com/licence/operator/'+login+'?callback=?',
					type: "GET",
					dataType: 'jsonp',
					cache: false,
					success: function (data, status, error) {
						if (data.error)
						{
							$('#livechat_already_have .ajax_message').removeClass('wait').addClass('message').html('Incorrect LiveChat login.');
							$('#livechat_login').focus();
							return false;
						}
						else
						{
							$('#license_number').val(data.number);
							$('#livechat_already_have form').submit();
						}
					},
					error: function (data, status, error) {
						$('#livechat_already_have .ajax_message').removeClass('wait').addClass('message').html('Try again.');
						$('#livechat_login').focus();
					}
				});
				return false;
			}
		});		
	},

	newLicenseForm: function()
	{
		$('#livechat_new_account form').submit(function()
		{
			if (parseInt($('#new_license_number').val()) > 0)
			{
				return true;
			}

			if (LiveChat.validateNewLicenseForm())
			{
				$('#livechat_new_account .ajax_message').removeClass('message').addClass('wait').html('Please wait&hellip;');

				// Check if email address is available
				$.getJSON('http://www.livechatinc.com/php/licence_info.php?email='+$('#email').val()+'&jsoncallback=?',
				function(response)
				{					
					if (response.response == 'true')
					{
						LiveChat.createLicense();
					}
					else if (response.response == 'false')
					{
						$('#livechat_new_account .ajax_message').removeClass('wait').addClass('message').html('This email address is already in use. Please choose another e-mail address.');
					}
					else
					{
						$('#livechat_new_account .ajax_message').removeClass('wait').addClass('message').html('Could not create account. Please try again later.');
					}
				});
			}

			return false;
		});
	},

	createLicense: function()
	{
		var url;

		$('#livechat_new_account .ajax_message').removeClass('message').addClass('wait').html('Creating new account&hellip;');

		url = 'https://www.livechatinc.com/signup/';
		url += '?name='+encodeURIComponent($('#name').val());
		url += '&email='+encodeURIComponent($('#email').val());
		url += '&password='+encodeURIComponent($('#password').val());
		url += '&website='+encodeURIComponent($('#website').val());
		url += '&timezone_gmt='+encodeURIComponent(this.calculateGMT());
		url += '&action=wordpress_signup';
		url += '&jsoncallback=?';

		$.getJSON(url, function(data)
		{
			data = parseInt(data.response);
			if (data == 0)
			{
				$('#livechat_new_account .ajax_message').html('Could not create account. Please try again later.').addClass('message').removeClass('wait');
				return false;
			}

			// save new licence number
			$('#new_license_number').val(data);
			$('#save_new_license').submit();
		});
	},

	validateNewLicenseForm: function()
	{
		if ($('#name').val().length < 1)
		{
			alert ('Please enter your name.');
			$('#name').focus();
			return false;
		}

		if (/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i.test($('#email').val()) == false)
		{
			alert ('Please enter a valid email address.');
			$('#email').focus();
			return false;
		}

		if ($.trim($('#password').val()).length < 6)
		{
			alert('Password must be at least 6 characters long');
			$('#password').focus();
			return false;
		}

		if ($('#password').val() !== $('#password_retype').val())
		{
			alert('Both passwords do not match.');
			$('#password').val('');
			$('#password_retype').val('');
			$('#password').focus();
			return false;
		}

		return true;
	},

	calculateGMT: function()
	{
		var date, dateGMTString, date2, gmt;

		date = new Date((new Date()).getFullYear(), 0, 1, 0, 0, 0, 0);
		dateGMTString = date.toGMTString();
		date2 = new Date(dateGMTString.substring(0, dateGMTString.lastIndexOf(" ")-1));
		gmt = ((date - date2) / (1000 * 60 * 60)).toString();

		return gmt;
	},

	controlPanelIframe: function()
	{
		var cp = $('#control_panel');
		if (cp.length)
		{
			var cp_resize = function()
			{
				var cp_height = window.innerHeight ? window.innerHeight : $(window).height();
				cp_height -= $('#wphead').height();
				cp_height -= $('#updated-nag').height();
				cp_height -= $('#control_panel + p').height();
				cp_height -= $('#footer').height();
				cp_height -= 70;

				cp.attr('height', cp_height);
			}
			cp_resize();
			$(window).resize(cp_resize);
		}
	},

	fadeChangesSaved: function()
	{
		$cs = $('#changes_saved_info');

		if ($cs.length)
		{
			setTimeout(function()
			{
				$cs.slideUp();
			}, 1000);
		}
	}
};

$(document).ready(function()
{
	LiveChat.init();
});
})(jQuery);