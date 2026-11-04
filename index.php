<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="theme-color" content="#101827">
  <title>CodeReview — Project Review Workspace</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="toast" class="toast" role="status" aria-live="polite"></div>

<section id="authView" class="auth-layout">
  <div class="auth-story">
    <a class="brand brand-light" href="#"><span class="brand-mark">CR</span><span>CodeReview</span></a>
    <div class="story-copy">
      <span class="eyebrow">BUILD · SUBMIT · IMPROVE</span>
      <h1>Better code<br><em>starts with</em><br>better feedback.</h1>
      <p>A focused workspace for developers to submit projects and receive clear, actionable code reviews.</p>
      <div class="story-points">
        <div><span class="point-icon">⌘</span><span><b>Project-based reviews</b><small>Keep every submission organized.</small></span></div>
        <div><span class="point-icon">⌁</span><span><b>Useful feedback</b><small>Know what to fix and why.</small></span></div>
        <div><span class="point-icon">↗</span><span><b>Visible progress</b><small>Track reviews from one dashboard.</small></span></div>
      </div>
    </div>
    <div class="story-footer">A learning space for developers and reviewers.</div>
  </div>
  <div class="auth-panel">
    <div class="auth-card">
      <div class="mobile-brand brand"><span class="brand-mark">CR</span><span>CodeReview</span></div>
      <div class="auth-heading">
        <span class="eyebrow" id="authEyebrow">WELCOME BACK</span>
        <h2 id="authTitle">Sign in to your workspace</h2>
        <p id="authSubtitle">Enter your account details to continue.</p>
      </div>
      <form id="authForm">
        <div id="nameField" class="field" hidden><label for="fullName">Full name</label><input id="fullName" name="name" autocomplete="name" placeholder="Your full name"></div>
        <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required></div>
        <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" placeholder="At least 8 characters" required minlength="8"></div>
        <button class="btn btn-primary btn-full" id="authSubmit" type="submit">Sign in <span>→</span></button>
      </form>
      <p class="auth-switch"><span id="switchPrompt">New to CodeReview?</span> <button id="switchAuth" class="text-button" type="button">Create an account</button></p>
      <p class="auth-note">Admin accounts are created by the site owner. Public registration creates developer accounts only.</p>
      <p id="authMessage" class="form-message" aria-live="polite"></p>
    </div>
  </div>
</section>

<div id="appView" class="app-shell" hidden>
  <aside class="sidebar" id="sidebar">
    <a class="brand brand-light sidebar-brand" href="#"><span class="brand-mark">CR</span><span>CodeReview</span></a>
    <div class="workspace-label">WORKSPACE</div>
    <nav class="side-nav">
      <button class="nav-item active" data-page="dashboard"><span class="nav-icon">▦</span>Overview</button>
      <button class="nav-item" data-page="projects"><span class="nav-icon">⌘</span><span id="projectsNavLabel">My projects</span></button>
      <button class="nav-item" data-page="reviews"><span class="nav-icon">☷</span><span id="reviewsNavLabel">My reviews</span></button>
    </nav>
    <div class="sidebar-bottom">
      <div class="sidebar-help"><span class="help-symbol">?</span><div><b>Need a hand?</b><small>Keep your code clear and organized.</small></div></div>
      <button id="logoutBtn" class="logout-button"><span>↪</span> Sign out</button>
    </div>
  </aside>

  <main class="main-area">
    <header class="topbar">
      <button id="mobileMenu" class="mobile-menu" aria-label="Open navigation">☰</button>
      <div><span class="topbar-kicker">CODE REVIEW WORKSPACE</span><h1 id="pageHeading">Overview</h1></div>
      <div class="topbar-user"><div class="avatar" id="userAvatar">U</div><div><b id="topUserName">User</b><small id="topUserRole">Developer</small></div></div>
    </header>

    <section id="dashboardPage" class="page">
      <div class="welcome-banner">
        <div><span class="eyebrow">YOUR WORKSPACE</span><h2 id="welcomeHeading">Welcome back.</h2><p id="welcomeDescription">Keep your projects moving forward.</p></div>
        <button id="quickSubmitBtn" class="btn btn-light">＋ New submission</button>
      </div>
      <div class="stats-grid">
        <article class="stat-card"><div class="stat-top"><span>Total projects</span><span class="stat-icon purple">⌘</span></div><strong id="statTotal">0</strong><small>Projects in the workspace</small></article>
        <article class="stat-card"><div class="stat-top"><span>Awaiting review</span><span class="stat-icon amber">◷</span></div><strong id="statPending">0</strong><small>Waiting for admin feedback</small></article>
        <article class="stat-card"><div class="stat-top"><span>Reviewed</span><span class="stat-icon green">✓</span></div><strong id="statReviewed">0</strong><small>Feedback is available</small></article>
      </div>
      <div class="section-heading"><div><span class="eyebrow">RECENT ACTIVITY</span><h3 id="recentTitle">Recent projects</h3></div><button class="text-button" data-goto="projects">View all →</button></div>
      <div id="recentList" class="project-list"><div class="empty-state">Loading projects…</div></div>
    </section>

    <section id="projectsPage" class="page" hidden>
      <div class="page-intro"><div><span class="eyebrow">PROJECT WORKSPACE</span><h2 id="projectsTitle">My projects</h2><p id="projectsDescription">Submit source files and follow their review status.</p></div><button id="newProjectBtn" class="btn btn-primary">＋ New project</button></div>
      <div class="toolbar"><div class="search-wrap"><span>⌕</span><input id="projectSearch" type="search" placeholder="Search projects…"></div><select id="statusFilter" aria-label="Filter by status"><option value="all">All statuses</option><option value="pending">Pending</option><option value="in_review">In review</option><option value="reviewed">Reviewed</option><option value="revision_requested">Revision requested</option><option value="rejected">Rejected</option></select></div>
      <div id="projectsList" class="project-list"><div class="empty-state">Loading projects…</div></div>
    </section>

    <section id="reviewsPage" class="page" hidden>
      <div class="page-intro"><div><span class="eyebrow">FEEDBACK CENTER</span><h2 id="reviewsTitle">My reviews</h2><p id="reviewsDescription">Read reviewer feedback and track next steps.</p></div></div>
      <div id="reviewsList" class="review-list"><div class="empty-state">Loading reviews…</div></div>
    </section>
  </main>
</div>

<dialog id="submissionDialog" class="modal">
  <form id="submissionForm" class="modal-card" enctype="multipart/form-data">
    <div class="modal-heading"><div><span class="eyebrow">NEW SUBMISSION</span><h2>Share a project</h2><p>Give your reviewer enough context to understand your work.</p></div><button type="button" class="icon-button" data-close="submissionDialog" aria-label="Close">×</button></div>
    <div class="field"><label for="projectTitle">Project name</label><input id="projectTitle" name="title" maxlength="150" placeholder="e.g. Personal portfolio website" required></div>
    <div class="field"><label for="projectDescription">Project description</label><textarea id="projectDescription" name="description" rows="3" maxlength="5000" placeholder="What does your project do? What would you like reviewed?" required></textarea></div>
    <div class="field"><label>Programming languages</label><div class="language-grid">
      <label><input type="checkbox" name="languages[]" value="HTML"> HTML</label><label><input type="checkbox" name="languages[]" value="CSS"> CSS</label><label><input type="checkbox" name="languages[]" value="JavaScript"> JavaScript</label><label><input type="checkbox" name="languages[]" value="PHP"> PHP</label><label><input type="checkbox" name="languages[]" value="Python"> Python</label><label><input type="checkbox" name="languages[]" value="Java"> Java</label><label><input type="checkbox" name="languages[]" value="C++"> C++</label><label><input type="checkbox" name="languages[]" value="SQL"> SQL</label><label><input type="checkbox" name="languages[]" value="Other"> Other</label>
    </div></div>
    <div class="field"><label for="sourceFiles">Project files</label><div class="upload-box"><span class="upload-icon">↑</span><div><b>Choose source files</b><small>HTML, CSS, JS, PHP, Python, Java, SQL, JSON and text · max 10 files, 5 MB each</small></div><input id="sourceFiles" name="files[]" type="file" multiple accept=".html,.htm,.css,.js,.jsx,.ts,.tsx,.php,.py,.java,.cpp,.c,.h,.cs,.sql,.json,.xml,.txt,.md,.vue,.tsv,.sh"></div><div id="fileNames" class="file-names">No files selected</div></div>
    <div class="modal-actions"><button type="button" class="btn btn-ghost" data-close="submissionDialog">Cancel</button><button type="submit" class="btn btn-primary" id="submitProjectBtn">Submit for review <span>→</span></button></div>
    <p id="submissionMessage" class="form-message"></p>
  </form>
</dialog>

<dialog id="detailDialog" class="modal modal-wide">
  <div class="modal-card">
    <div class="modal-heading"><div><span class="eyebrow">SUBMISSION DETAILS</span><h2 id="detailTitle">Project</h2><p id="detailMeta"></p></div><button type="button" class="icon-button" data-close="detailDialog" aria-label="Close">×</button></div>
    <p id="detailDescription" class="detail-description"></p>
    <div id="detailFiles" class="detail-files"></div>
    <div id="detailReview" class="detail-review"></div>
    <div class="modal-actions"><button type="button" class="btn btn-ghost" data-close="detailDialog">Close</button></div>
  </div>
</dialog>

<dialog id="reviewDialog" class="modal modal-wide">
  <form id="reviewForm" class="modal-card">
    <div class="modal-heading"><div><span class="eyebrow">ADMIN REVIEW</span><h2 id="reviewProjectTitle">Review project</h2><p id="reviewDeveloper"></p></div><button type="button" class="icon-button" data-close="reviewDialog" aria-label="Close">×</button></div>
    <div id="reviewCodeFiles" class="review-code-files"></div>
    <input type="hidden" id="reviewSubmissionId">
    <div class="field"><label for="reviewStatus">Review outcome</label><select id="reviewStatus"><option value="reviewed">Reviewed — ready</option><option value="revision_requested">Revision requested</option><option value="rejected">Rejected</option></select></div>
    <div class="field"><label for="reviewFeedback">Feedback</label><textarea id="reviewFeedback" rows="5" maxlength="10000" placeholder="Explain what works, what needs improvement, and actionable next steps…" required></textarea></div>
    <div class="modal-actions"><button type="button" class="btn btn-ghost" data-close="reviewDialog">Cancel</button><button type="submit" class="btn btn-primary" id="saveReviewBtn">Save review <span>→</span></button></div>
    <p id="reviewFormMessage" class="form-message"></p>
  </form>
</dialog>

<script src="assets/js/app.js" defer></script>
</body>
</html>
