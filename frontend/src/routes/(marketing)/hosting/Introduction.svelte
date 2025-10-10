<h1>Hosting: Get Started</h1>

<h2 id="relay">Relay Setup</h2>

<p>
	Hyvor Post sends email using Hyvor Relay. You can use Hyvor Relay Cloud or a self-hosted
	instance.
</p>

<ul>
	<li>
		Create a new project in Hyvor Relay. Ideally, select the type "distributional" if that queue
		is enabled on your instance.
	</li>
	<li>
		Create an API key with the following scopes:

		<ul>
			<li>
				<code>sends.send</code>
			</li>
			<li>
				<code>domains.read</code>
			</li>
			<li>
				<code>domains.write</code>
			</li>
		</ul>
	</li>
	<li>
		Set the following environment variables in Hyvor Post:
		<ul>
			<li>
				<code>RELAY_URL</code>: URL of the Relay instance. For cloud, use
				<code>https://relay.hyvor.com</code>
			</li>
			<li><code>RELAY_API_KEY</code>: The API key created above.</li>
		</ul>
	</li>
	<li>
		Then, in Hyvor Relay, add and verify the domain you will be sending system emails from and
		set <code>SYSTEM_MAIL_FROM</code> environment variable in Hyvor Post to an email address in
		that domain. Ex:
		<ul>
			<li>Domain: example.com</li>
			<li>
				<code>SYSTEM_MAIL_FROM</code>: no-reply@example.com
			</li>
		</ul>
		<p>
			Note that users of Hyvor Post <strong>cannot setup</strong> this domain in their
			newsletters. Optionally, you can also set <code>SYSTEM_MAIL_REPLY_TO</code> env variable
			to set the reply-to address for system emails.
		</p>
	</li>
</ul>
