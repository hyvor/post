<script lang="ts">
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import IconReply from '@hyvor/icons/IconReply';
	import IconForward from '@hyvor/icons/IconForward';
	import IconArchive from '@hyvor/icons/IconArchive';
	import ppImg from '../img/pp.svg';
	import { Avatar } from '@hyvor/design/components';
	export let emailImage = '';
	export let subject = 'Weekly Newsletter';
	export let sender = 'newsletter@company.com';
	export let date = new Date().toLocaleDateString('en-US', {
		weekday: 'short',
		year: 'numeric',
		month: 'short',
		day: 'numeric',
		hour: '2-digit',
		minute: '2-digit'
	});
	export let isStarred = false;
	export let isRead = true;
	export let priority = 'normal'; // 'high', 'normal', 'low'

	let isExpanded = true; // Always start expanded

	function toggleExpanded() {
		isExpanded = !isExpanded;
	}

	function toggleStar() {
		isStarred = !isStarred;
	}
</script>

<div class="email-container" class:unread={!isRead} class:expanded={isExpanded}>
	<!-- Email Header -->
	<div class="email-header" on:click={toggleExpanded}>
		<div class="header-left">
			<div class="sender-info">
				<div class="sender-avatar">
					<div class="avatar-icon">
						<Avatar src={ppImg} size="medium" />
					</div>
				</div>
				<div class="sender-details">
					<div class="sender-name">{sender}</div>
					<div class="email-date">{date}</div>
				</div>
			</div>
			<div class="subject-line" class:priority-high={priority === 'high'}>
				<!-- {#if priority === 'high'}
					<span class="priority-indicator">🔴</span>
				{/if} -->
				{subject}
			</div>
		</div>

		<div class="header-actions">
			<button
				class="star-btn"
				class:starred={isStarred}
				on:click|stopPropagation={toggleStar}
				aria-label={isStarred ? 'Remove star' : 'Add star'}
			>
				{isStarred ? '⭐' : '☆'}
			</button>

			<div class="expand-indicator" class:rotated={isExpanded}>
				<!-- <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
					<path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z" />
				</svg> -->
				<IconChevronDown />
			</div>
		</div>
	</div>

	<!-- Email Content -->
	<div class="email-content" class:visible={isExpanded}>
		<div class="email-toolbar">
			<div class="toolbar-actions">
				<button class="action-btn reply">
					<IconReply />
					Reply
				</button>
				<button class="action-btn forward">
					<IconForward />
					Forward
				</button>
				<button class="action-btn archive">
					<!-- <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
						<path
							d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"
						/>
					</svg> -->
					<IconArchive />
					Archive
				</button>
			</div>
		</div>

		<div class="email-body">
			{#if emailImage}
				<div class="email-image-container">
					<img src={emailImage} alt="Newsletter content" class="newsletter-image" />
				</div>
			{:else}
				<div class="email-placeholder">
					<div class="placeholder-header">
						<div class="placeholder-logo">📰</div>
						<h2>Newsletter Content</h2>
					</div>

					<div class="placeholder-content">
						<div class="content-section">
							<div class="section-title"></div>
							<div class="content-lines">
								<div class="content-line long"></div>
								<div class="content-line medium"></div>
								<div class="content-line short"></div>
							</div>
						</div>

						<div class="content-section">
							<div class="section-title"></div>
							<div class="content-lines">
								<div class="content-line medium"></div>
								<div class="content-line long"></div>
								<div class="content-line medium"></div>
							</div>
						</div>

						<div class="cta-section">
							<div class="cta-button">Read More</div>
						</div>
					</div>
				</div>
			{/if}
		</div>
	</div>
</div>

<style>
	.email-container {
		max-width: 800px;
		margin: 20px auto;
		background: white;
		border-radius: 12px;
		box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
		border: 1px solid #e1e5e9;
		overflow: hidden;
		transition: all 0.3s ease;
	}

	.email-container:hover {
		box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
	}

	.email-container.unread {
		border-left: 4px solid #1a73e8;
		background: #f8f9ff;
	}

	/* Email Header */
	.email-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 16px 20px;
		cursor: pointer;
		transition: background-color 0.2s ease;
		border-bottom: 1px solid transparent;
	}

	.email-header:hover {
		background-color: #f8f9fa;
	}

	.email-container.expanded .email-header {
		border-bottom: 1px solid #e1e5e9;
	}

	.header-left {
		display: flex;
		align-items: center;
		gap: 16px;
		flex: 1;
	}

	.sender-info {
		display: flex;
		align-items: center;
		gap: 12px;
		min-width: 200px;
	}

	.sender-avatar {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		/* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.avatar-icon {
		font-size: 18px;
	}

	.sender-details {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}

	.sender-name {
		font-weight: 600;
		color: #202124;
		font-size: 14px;
	}

	.email-date {
		font-size: 12px;
		color: #5f6368;
	}

	.subject-line {
		font-weight: 500;
		color: #202124;
		font-size: 14px;
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.subject-line.priority-high {
		color: #d93025;
	}

	.priority-indicator {
		font-size: 12px;
	}

	.header-actions {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.star-btn {
		background: none;
		border: none;
		cursor: pointer;
		font-size: 18px;
		padding: 4px;
		border-radius: 4px;
		transition: background-color 0.2s ease;
	}

	.star-btn:hover {
		background-color: #f1f3f4;
	}

	.star-btn.starred {
		color: #fbbc04;
	}

	.expand-indicator {
		transition: transform 0.3s ease;
		color: #5f6368;
	}

	.expand-indicator.rotated {
		transform: rotate(180deg);
	}

	/* Email Content */
	.email-content {
		max-height: 0;
		overflow: hidden;
		transition: max-height 0.4s ease;
	}

	.email-content.visible {
		max-height: 1000px;
	}

	.email-toolbar {
		padding: 12px 20px;
		background: #f8f9fa;
		border-bottom: 1px solid #e1e5e9;
	}

	.toolbar-actions {
		display: flex;
		gap: 12px;
	}

	.action-btn {
		display: flex;
		align-items: center;
		gap: 6px;
		background: white;
		border: 1px solid #dadce0;
		border-radius: 6px;
		padding: 8px 12px;
		cursor: pointer;
		font-size: 13px;
		color: #3c4043;
		transition: all 0.2s ease;
	}

	.action-btn:hover {
		background: #f1f3f4;
		border-color: #c4c7c5;
	}

	.email-body {
		padding: 20px;
	}

	/* Newsletter Image */
	.email-image-container {
		border-radius: 8px;
		overflow: hidden;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	}

	.newsletter-image {
		width: 100%;
		height: auto;
		display: block;
	}

	/* Placeholder Content */
	.email-placeholder {
		text-align: center;
		color: #5f6368;
	}

	.placeholder-header {
		margin-bottom: 30px;
	}

	.placeholder-logo {
		font-size: 48px;
		margin-bottom: 16px;
	}

	.placeholder-header h2 {
		color: #202124;
		font-size: 24px;
		font-weight: 500;
		margin: 0;
	}

	.placeholder-content {
		display: flex;
		flex-direction: column;
		gap: 24px;
		text-align: left;
	}

	.content-section {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	.section-title {
		width: 30%;
		height: 20px;
		background: linear-gradient(90deg, #f1f3f4 0%, #e8eaed 100%);
		border-radius: 4px;
	}

	.content-lines {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.content-line {
		height: 14px;
		background: linear-gradient(90deg, #f8f9fa 0%, #f1f3f4 100%);
		border-radius: 3px;
	}

	.content-line.short {
		width: 45%;
	}
	.content-line.medium {
		width: 70%;
	}
	.content-line.long {
		width: 95%;
	}

	.cta-section {
		text-align: center;
		margin-top: 20px;
	}

	.cta-button {
		display: inline-block;
		background: linear-gradient(135deg, #1a73e8 0%, #4285f4 100%);
		color: white;
		padding: 12px 24px;
		border-radius: 6px;
		font-weight: 500;
		font-size: 14px;
	}

	/* Responsive */
	@media (max-width: 768px) {
		.email-container {
			margin: 10px;
			border-radius: 8px;
		}

		.sender-info {
			min-width: auto;
			flex: 1;
		}

		.subject-line {
			font-size: 13px;
		}

		.toolbar-actions {
			flex-wrap: wrap;
			gap: 8px;
		}

		.action-btn {
			font-size: 12px;
			padding: 6px 10px;
		}
	}
</style>
