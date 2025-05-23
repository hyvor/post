<script>
	import { Document } from '@hyvor/design/marketing';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
	// import { IconCardChecklist } from '@hyvor/design/icons';
</script>

<Document title="Privacy Policy (Hyvor Talk)" subtitle="Last Updated: 2024-08-01">
	<p>
		This Privacy Policy extends the general privacy policy available at <a
			href="https://hyvor.com/privacy">https://hyvor.com/privacy</a
		>. It specifically addresses how Hyvor Talk collects, uses, and protects data.
	</p>

	<Callout>
		<!-- align bug -->
		<span slot="icon" style="margin-top: 8px;display:inline-flex;align-items:center;">
			<!-- <IconCardChecklist size={30} /> -->
		</span>
		<div slot="title">Non-binding Summary</div>

		<ul>
			<li>HYVOR is a privacy-focused company.</li>
			<li>We only collect data necessary for the operation of Hyvor Talk.</li>
			<li>
				<strong>In embeds</strong> that you place on your website, no analytics, advertising,
				or tracking scripts are used.
			</li>
			<li>
				<strong>On our website</strong>, we just use one tool to catch errors, nothing more
				- not even analytics.
			</li>
			<li>
				We only share your data with third parties when necessary for the operation of Hyvor
				Talk. This includes email delivery, spam detection, and payment processing.
			</li>
			<li>We never sell data to third parties.</li>
		</ul>
	</Callout>

	<hr />

	<h2 id="embeds">1. Embeds</h2>

	<p>This section covers all data collected through Hyvor Post embeds on customer websites.</p>

	<h3 id="embeds-data">1.1. Data Collected Through Embeds</h3>

	<h4 id="embeds-data-user-account-information">1.1.1. User Account Information</h4>

	<ul>
		<li>
			<strong>HYVOR Users:</strong>
			Data collected at hyvor.com (refer to HYVOR privacy policy for details).
		</li>
	</ul>

	<h4 id="ip-address">1.1.2. IP Address</h4>

	<ul>
		<li>Collected if enabled by the website owner</li>
		<li>Used for abuse prevention and spam detection</li>
		<li>
			For logged in users, recorded at the time of first action (comment, upvote, etc.) and
			updated on each action.
		</li>
	</ul>

	<!-- <h4 id="embeds-data-interaction-data">1.1.4. Interaction Data</h4> -->

	<!-- <ul>
		<li>
			<strong>Reactions:</strong> User's reaction choice
		</li>
		<li>
			<strong>Ratings:</strong> Star ratings provided by users
		</li>
		<li>
			<strong>Votes:</strong> Upvotes and downvotes on comments
		</li>
		<li>
			<strong>Flags:</strong> Flags on comments
		</li>
		<li>
			<strong> Dates and times of actions: </strong> When a user comments, upvotes, downvotes,
			etc.
		</li>
	</ul> -->

	<h4 id="embeds-data-newsletter-subscriptions">1.1.3. Newsletter Subscriptions</h4>

	<ul>
		<li>Email addresses of users who subscribe through our newsletter form</li>
	</ul>

	<h3 id="embeds-data-use">1.2. Use of Collected Data</h3>

	<p>We use the data collected through embeds to:</p>

	<ul>
		<li>Provide and improve the Hyvor Post service</li>
		<li>Prevent abuse and detect spam</li>
		<li>Send newsletters (if subscribed)</li>
		<li>Generate reports for website owners</li>
		<li>Comply with legal obligations</li>
	</ul>

	<h3 id="embeds-data-retention">1.3. Data Retention</h3>

	<ul>
		<li>
			Data collected through embeds is retained for as long as necessary to provide our
			services, comply with legal obligations, or protect our legitimate business interests.
		</li>
		<li>
			Users may request deletion of their data by contacting the website owner or Hyvor Post
			support.
		</li>
	</ul>

	<h3 id="embeds-data-sharing">1.4. Data Sharing</h3>

	<p>Data collected through embeds may be shared with:</p>

	<ul>
		<li>Website owners (limited to data related to their website)</li>
		<li>Service providers (e.g., hosting providers, email services)</li>
		<li>Law enforcement or regulatory bodies when required by law</li>
	</ul>

	<h3 id="embeds-cookies">1.5. Cookies and Local Storage</h3>

	<p>Hyvor Post embed uses the following cookies and local storage to provide its services:</p>

	<Table columns="1fr 2fr 1fr 1fr">
		<TableRow head>
			<div>Name</div>
			<div>Purpose</div>
			<div>Expiration</div>
			<div>Storage</div>
		</TableRow>
		<TableRow>
			<div>
				<!-- <code>ht_{'{id}'}_token</code> -->
			</div>
			<div>Authentication token for the user for the current website ID.</div>
			<div>180 days</div>
			<div>Local Storage</div>
		</TableRow>
		<TableRow>
			<div>
				<code>postsess</code>
			</div>
			<div>
				Used to store temporary session data. For example, while OpenID Connect
				authentication.
			</div>
			<div>15 minutes</div>
			<div>Cookie</div>
		</TableRow>
		<TableRow>
			<div>
				<code>authsess</code>
			</div>
			<div>
				This is the HYVOR authentication cookie. See <a href="https://hyvor.com/privacy"
					>HYVOR privacy policy</a
				> for details.
			</div>
			<div></div>
			<div>Cookie</div>
		</TableRow>
	</Table>

	<p>In addition, embeds may use local storage to save user preferences and comment drafts.</p>

	<hr />
	<h2 id="website">2. Website</h2>

	<p>
		This section covers data collected from users of our website at post.hyvor.com, including
		website owners, moderators, and visitors who interact with our marketing pages, the Console,
		documentation, and other hosted pages at post.hyvor.com.
	</p>

	<h3 id="website-data">2.1. Data Collected on Our Website</h3>

	<ul>
		<li>
			For logged in users, the data is collected at hyvor.com. Please refer to the HYVOR
			privacy policy for details.
		</li>
		<li>
			Additional usage data and preferences may be collected when using the Console,
			documentation, and other pages.
		</li>
	</ul>

	<h3 id="third-party-services">2.2. Third-Party Services</h3>

	<p>We use the following third-party services on our website:</p>

	<ul>
		<li>
			<a href="https://sentry.io">Sentry</a> for error tracking and debugging, which may collect
			information about the user's device and browser.
		</li>
		
	</ul>

	<p>
		These services may collect data according to their own privacy policies. Please refer to
		their privacy policies for more information.
	</p>

	<hr />

	<p>
		For general information about how we collect, use, and protect data, please refer to the
		general privacy policy available at <a href="https://hyvor.com/privacy" target="_blank"
			>https://hyvor.com/privacy</a
		>. All our sub-processors are listed at
		<a href="https://hyvor.com/sub" target="_blank">https://hyvor.com/sub</a>.
	</p>
</Document>

<style>
	h4 {
		font-size: 20px;
	}

	code {
		font-size: 14px;
		padding: 0.2em 0.4em;
		display: inline-block;
		background-color: #f4f2f0;
		color: #905;
		font-family: inherit;
		border-radius: 4px;
		line-height: normal;
		font-weight: 400;
	}
</style>
