$header
	<form method="post" action="$filename">
		<input type="hidden" name="s" value="$s" />
		<input type="hidden" name="page" value="$page" />
		<input type="hidden" name="action" value="$action" />
		<table cellpadding="5" cellspacing="4" border="0" align="center" class="maintable">
			<tr>
				<td class="maintitle" colspan="2"><b><img src="images/title.gif" alt="" />&nbsp;{$lng['admin']['domain_add']}</b></td>
			</tr>
			<tr>
				<td class="main_field_name">{$lng['admin']['customer']}:</td>
				<td class="main_field_display" nowrap="nowrap"><select class="dropdown_noborder" name="customerid">$customers</select></td>
			</tr>
			<tr>
				<td class="main_field_name">Domain:</td>
				<td class="main_field_display" nowrap="nowrap"><input type="text" name="domain" value="" size="60" /></td>
			</tr>
			<tr>
				<td class="main_field_name">{$lng['domains']['aliasdomain']}:</td>
				<td class="main_field_display" nowrap="nowrap"><select class="dropdown_noborder" name="alias">$domains</select></td>
			</tr>
			<if $userinfo['change_serversettings'] == '1'>
			<tr>
				<td class="main_field_name">DocumentRoot:<br />({$lng['panel']['emptyfordefault']})</td>
				<td class="main_field_display" nowrap="nowrap"><input type="text" name="documentroot" value="" size="60" /></td>
			</tr>
			<tr>
				<td class="main_field_name">IP/Port:</td>
				<td class="main_field_display" nowrap="nowrap"><select name="ipandport">$ipsandports</select></td>
			</tr>
			<tr>
				<td class="main_field_name">Nameserver:</td>
				<td class="main_field_display" nowrap="nowrap">$isbinddomain</td>
			</tr>
			<tr>
				<td class="main_field_name">Zonefile:<br />({$lng['panel']['emptyfordefault']})</td>
				<td class="main_field_display" nowrap="nowrap"><input type="text" name="zonefile" value="" size="60" /></td>
			</tr>
			</if>
			<if $settings['system']['use_ssl'] == '1'>
			<tr>
				<td class="main_field_name">SSL:</td>
				<td class="main_field_display" nowrap="nowrap">$ssl</td>
			</tr>
			<tr>
				<td class="main_field_name">SSL Redirect:</td>
				<td class="main_field_display" nowrap="nowrap">$ssl_redirect</td>
			</tr>
			<tr>
				<td class="main_field_name">SSL IP/Port:</td>
				<if $show_ssl_ipsandports == 1>
				<td class="main_field_display" nowrap="nowrap"><select class="dropdown_noborder" name="ssl_ipandport">$ssl_ipsandports</select></td>
				<else>
				<td class="main_field_display" nowrap="nowrap">{$lng['panel']['nosslipsavailable']}</td>
				</if>
			</tr>
			<tr>
				<td class="main_field_name">{$lng['admin']['wwwserveralias']}:</td>
				<td class="main_field_display" nowrap="nowrap">$wwwserveralias</td>
			</tr>
			</if>
			<tr>
				<td class="main_field_name">{$lng['admin']['emaildomain']}:</td>
				<td class="main_field_display" nowrap="nowrap">$isemaildomain</td>
			</tr>
			<tr>
				<td class="main_field_name">{$lng['admin']['email_only']}:</td>
				<td class="main_field_display" nowrap="nowrap">$isemail_only</td>
			</tr>
			<tr>
				<td class="main_field_name">{$lng['admin']['subdomainforemail']}:</td>
				<td class="main_field_display" nowrap="nowrap"><select class="dropdown_noborder" name="subcanemaildomain">$subcanemaildomain</select></td>
			</tr>
			<if $settings['dkim']['use_dkim'] == '1'>
 			<tr>
				<td class="main_field_name">DomainKeys:</td>
				<td class="main_field_display" nowrap="nowrap">$dkim</td>
			</tr>
			</if>
			<tr>
				<td class="main_field_name">{$lng['admin']['domain_edit']}:</td>
				<td class="main_field_display" nowrap="nowrap">$caneditdomain</td>
			</tr>
			<if $userinfo['change_serversettings'] == '1' || $userinfo['caneditphpsettings'] == '1'>
			<tr>
				<td class="main_field_name">OpenBasedir:</td>
				<td class="main_field_display" nowrap="nowrap">$openbasedir</td>
			</tr>
			<tr>
				<td class="main_field_name">Safemode:</td>
				<td class="main_field_display" nowrap="nowrap">$safemode</td>
			</tr>
			</if>
			<if $userinfo['change_serversettings'] == '1'>
			<tr>
				<td class="main_field_name">Speciallogfile:</td>
				<td class="main_field_display" nowrap="nowrap">$speciallogfile</td>
			</tr>
			<tr>
				<td class="main_field_name">{$lng['admin']['ownvhostsettings']}:</td>
				<td class="main_field_display" nowrap="nowrap"><textarea class="textarea_noborder" rows="12" cols="60" name="specialsettings">{$settings['system']['default_vhostconf']}</textarea></td>
			</tr>
			</if>
			<tr>
				<td class="main_field_confirm" colspan="2"><input type="hidden" name="send" value="send" /><input class="bottom" type="submit" value="{$lng['panel']['save']}" /></td>
			</tr>
		</table>
	</form>
	<br />
	<br />
$footer