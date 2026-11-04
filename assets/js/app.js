const API = 'api';
let currentUser = null;
let csrfToken = '';
let submissions = [];
let reviews = [];
let authMode = 'login';

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => [...document.querySelectorAll(selector)];

function escapeHTML(value = '') {
  return String(value).replace(/[&<>"']/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  })[char]);
}
function formatDate(value) {
  if (!value) return '—';
  const date = new Date(value.replace(' ', 'T'));
  return Number.isNaN(date.getTime()) ? value : date.toLocaleString([], {dateStyle:'medium',timeStyle:'short'});
}
function formatBytes(bytes) {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024*1024) return `${(bytes/1024).toFixed(1)} KB`;
  return `${(bytes/1024/1024).toFixed(1)} MB`;
}
function toast(message, type = 'success') {
  const node = $('#toast');
  node.textContent = message;
  node.className = `toast show ${type}`;
  clearTimeout(toast.timer);
  toast.timer = setTimeout(() => node.classList.remove('show'), 3500);
}
async function api(path, options = {}) {
  const headers = new Headers(options.headers || {});
  if (csrfToken && options.method && options.method !== 'GET') headers.set('X-CSRF-Token', csrfToken);
  if (options.body && !(options.body instanceof FormData)) headers.set('Content-Type', 'application/json');
  const response = await fetch(`${API}/${path}`, {...options, headers, credentials:'same-origin'});
  let data;
  try { data = await response.json(); } catch { throw new Error('The server returned an invalid response. Check your PHP error log.'); }
  if (data.csrf_token) csrfToken = data.csrf_token;
  if (!response.ok || data.success === false) throw new Error(data.message || 'Something went wrong.');
  return data;
}
function setAuthMode(mode) {
  authMode = mode;
  const register = mode === 'register';
  $('#nameField').hidden = !register;
  $('#fullName').required = register;
  $('#authEyebrow').textContent = register ? 'CREATE YOUR ACCOUNT' : 'WELCOME BACK';
  $('#authTitle').textContent = register ? 'Start your developer journey' : 'Sign in to your workspace';
  $('#authSubtitle').textContent = register ? 'Create an account to submit projects and receive feedback.' : 'Enter your account details to continue.';
  $('#authSubmit').innerHTML = register ? 'Create account <span>→</span>' : 'Sign in <span>→</span>';
  $('#switchPrompt').textContent = register ? 'Already have an account?' : 'New to CodeReview?';
  $('#switchAuth').textContent = register ? 'Sign in' : 'Create an account';
  $('#password').autocomplete = register ? 'new-password' : 'current-password';
  $('#authMessage').textContent = '';
}
function showAuth(message = '') {
  currentUser = null;
  $('#appView').hidden = true;
  $('#authView').hidden = false;
  if (message) $('#authMessage').textContent = message;
}
function showApp() {
  $('#authView').hidden = true;
  $('#appView').hidden = false;
  const isAdmin = currentUser.role === 'admin';
  $('#topUserName').textContent = currentUser.name;
  $('#topUserRole').textContent = isAdmin ? 'Administrator' : 'Developer';
  $('#userAvatar').textContent = currentUser.name.trim().charAt(0).toUpperCase();
  $('#welcomeHeading').textContent = `Welcome back, ${currentUser.name.split(' ')[0]}.`;
  $('#welcomeDescription').textContent = isAdmin ? 'Here is what is happening across the review workspace.' : 'Keep learning, submitting, and improving your code.';
  $('#projectsNavLabel').textContent = isAdmin ? 'All submissions' : 'My projects';
  $('#reviewsNavLabel').textContent = isAdmin ? 'Review activity' : 'My reviews';
  $('#projectsTitle').textContent = isAdmin ? 'All submissions' : 'My projects';
  $('#projectsDescription').textContent = isAdmin ? 'Open a submission to inspect files and review developer work.' : 'Submit source files and follow their review status.';
  $('#reviewsTitle').textContent = isAdmin ? 'Review activity' : 'My reviews';
  $('#reviewsDescription').textContent = isAdmin ? 'A history of feedback sent to developers.' : 'Read reviewer feedback and track your next steps.';
  $('#quickSubmitBtn').hidden = isAdmin;
  $('#newProjectBtn').hidden = isAdmin;
  $('#statusFilter').value = 'all';
  loadWorkspace();
}
async function loadWorkspace() {
  try {
    const [projectData, reviewData] = await Promise.all([
      api('submissions/list.php'),
      api('reviews/list.php')
    ]);
    submissions = projectData.submissions || [];
    reviews = reviewData.reviews || [];
    renderDashboard();
    renderProjects();
    renderReviews();
  } catch (error) { toast(error.message, 'error'); }
}
function statusLabel(status) {
  return ({
    pending:'Pending', in_review:'In review', reviewed:'Reviewed',
    revision_requested:'Revision requested', rejected:'Rejected'
  })[status] || status;
}
function statusClass(status) { return `status-${status || 'pending'}`; }
function projectCard(item) {
  const langs = (item.languages || []).map(x => `<span class="tag">${escapeHTML(x)}</span>`).join('');
  const action = currentUser.role === 'admin'
    ? `<button class="btn btn-small btn-primary" data-review="${item.id}">Open review →</button>`
    : `<button class="btn btn-small btn-ghost" data-detail="${item.id}">View details →</button>`;
  return `<article class="project-card">
    <div class="project-card-main"><div class="project-card-title"><div class="project-symbol">⌘</div><div><h3>${escapeHTML(item.title)}</h3><p>${currentUser.role === 'admin' ? `Submitted by ${escapeHTML(item.developer_name)}` : escapeHTML(item.description)}</p></div></div>
    <div class="project-meta"><span>▧ ${(item.files || []).length} file(s)</span><span>◷ ${formatDate(item.created_at)}</span></div><div class="tag-row">${langs}</div></div>
    <div class="project-card-side"><span class="status-pill ${statusClass(item.status)}">${statusLabel(item.status)}</span>${action}</div>
  </article>`;
}
function renderDashboard() {
  const total = submissions.length;
  const pending = submissions.filter(x => ['pending','in_review'].includes(x.status)).length;
  const done = total - pending;
  $('#statTotal').textContent = total;
  $('#statPending').textContent = pending;
  $('#statReviewed').textContent = done;
  const recent = submissions.slice(0,4);
  $('#recentTitle').textContent = currentUser.role === 'admin' ? 'Latest submissions' : 'Your latest projects';
  $('#recentList').innerHTML = recent.length ? recent.map(projectCard).join('') : `<div class="empty-state"><span class="empty-icon">⌘</span><b>No projects yet</b><p>${currentUser.role === 'admin' ? 'Developer submissions will appear here.' : 'Create your first project submission to get started.'}</p></div>`;
}
function renderProjects() {
  const query = $('#projectSearch').value.trim().toLowerCase();
  const filter = $('#statusFilter').value;
  const items = submissions.filter(x => (filter === 'all' || x.status === filter) && `${x.title} ${x.description} ${x.developer_name || ''}`.toLowerCase().includes(query));
  $('#projectsList').innerHTML = items.length ? items.map(projectCard).join('') : `<div class="empty-state"><span class="empty-icon">⌕</span><b>No matching projects</b><p>Try changing your search or status filter.</p></div>`;
}
function renderReviews() {
  $('#reviewsList').innerHTML = reviews.length ? reviews.map(r => `<article class="review-item">
    <div class="review-item-top"><div><span class="eyebrow">${currentUser.role === 'admin' ? `DEVELOPER: ${escapeHTML(r.developer_name || '')}` : `REVIEW FROM ${escapeHTML(r.admin_name || 'ADMIN')}`}</span><h3>${escapeHTML(r.project_title)}</h3></div><span class="status-pill ${statusClass(r.status)}">${statusLabel(r.status)}</span></div>
    <p>${escapeHTML(r.feedback)}</p><small>${formatDate(r.created_at)}</small>
  </article>`).join('') : `<div class="empty-state"><span class="empty-icon">☷</span><b>No reviews yet</b><p>${currentUser.role === 'admin' ? 'Your submitted reviews will appear here.' : 'When an admin reviews your project, the feedback will appear here.'}</p></div>`;
}
function navigate(page) {
  $$('.page').forEach(p => p.hidden = p.id !== `${page}Page`);
  $$('.nav-item').forEach(b => b.classList.toggle('active', b.dataset.page === page));
  $('#pageHeading').textContent = ({dashboard:'Overview',projects:currentUser.role === 'admin'?'All submissions':'My projects',reviews:currentUser.role === 'admin'?'Review activity':'My reviews'})[page];
  $('#sidebar').classList.remove('open');
  if (page === 'projects') renderProjects();
}
async function openDetail(id) {
  try {
    const data = await api(`submissions/detail.php?id=${encodeURIComponent(id)}`);
    const item = data.submission;
    $('#detailTitle').textContent = item.title;
    $('#detailMeta').textContent = `${item.developer_name} · ${statusLabel(item.status)} · ${formatDate(item.created_at)}`;
    $('#detailDescription').textContent = item.description;
    $('#detailFiles').innerHTML = (item.files || []).map(f => `<details class="file-detail"><summary><span>▤ ${escapeHTML(f.file_name)}</span><small>${formatBytes(f.file_size)}</small></summary><pre>${escapeHTML(f.content || '')}</pre></details>`).join('') || '<p>No files attached.</p>';
    $('#detailReview').innerHTML = item.review ? `<div class="feedback-box"><b>Latest feedback · ${escapeHTML(item.review.admin_name || 'Admin')}</b><p>${escapeHTML(item.review.feedback)}</p></div>` : '';
    $('#detailDialog').showModal();
  } catch (e) { toast(e.message,'error'); }
}
async function openReview(id) {
  try {
    const data = await api(`submissions/detail.php?id=${encodeURIComponent(id)}`);
    const item = data.submission;
    $('#reviewSubmissionId').value = item.id;
    $('#reviewProjectTitle').textContent = item.title;
    $('#reviewDeveloper').textContent = `Submitted by ${item.developer_name} · ${formatDate(item.created_at)}`;
    $('#reviewCodeFiles').innerHTML = (item.files || []).map((f,i) => `<details class="file-detail" ${i===0?'open':''}><summary><span>▤ ${escapeHTML(f.file_name)}</span><small>${formatBytes(f.file_size)}</small></summary><pre>${escapeHTML(f.content || '')}</pre></details>`).join('');
    $('#reviewStatus').value = item.status === 'pending' || item.status === 'in_review' ? 'reviewed' : item.status;
    $('#reviewFeedback').value = item.review?.feedback || '';
    $('#reviewFormMessage').textContent = '';
    $('#reviewDialog').showModal();
  } catch (e) { toast(e.message,'error'); }
}

$('#switchAuth').addEventListener('click', () => setAuthMode(authMode === 'login' ? 'register' : 'login'));
$('#authForm').addEventListener('submit', async event => {
  event.preventDefault();
  const button = $('#authSubmit'); button.disabled = true;
  const payload = {email:$('#email').value.trim(),password:$('#password').value};
  if (authMode === 'register') payload.name = $('#fullName').value.trim();
  try {
    if (authMode === 'register') {
      await api('auth/register.php',{method:'POST',body:JSON.stringify(payload)});
      setAuthMode('login'); $('#authMessage').textContent = 'Account created. Sign in with your new details.'; $('#authMessage').className = 'form-message success';
      $('#password').value = '';
    } else {
      const data = await api('auth/login.php',{method:'POST',body:JSON.stringify(payload)});
      currentUser = data.user; csrfToken = data.csrf_token; showApp(); navigate('dashboard');
    }
  } catch (e) { $('#authMessage').textContent = e.message; $('#authMessage').className = 'form-message error'; }
  finally { button.disabled = false; }
});
$('#logoutBtn').addEventListener('click', async () => {
  try { await api('auth/logout.php',{method:'POST',body:'{}'}); } catch {}
  showAuth(); setAuthMode('login');
});
$$('.nav-item').forEach(button => button.addEventListener('click', () => navigate(button.dataset.page)));
$$('[data-goto]').forEach(button => button.addEventListener('click', () => navigate(button.dataset.goto)));
$('#quickSubmitBtn').addEventListener('click', () => $('#submissionDialog').showModal());
$('#newProjectBtn').addEventListener('click', () => $('#submissionDialog').showModal());
$('#projectSearch').addEventListener('input', renderProjects);
$('#statusFilter').addEventListener('change', renderProjects);
$('#projectsList').addEventListener('click', event => {
  const detail = event.target.closest('[data-detail]');
  const review = event.target.closest('[data-review]');
  if (detail) openDetail(detail.dataset.detail);
  if (review) openReview(review.dataset.review);
});
$('#recentList').addEventListener('click', event => {
  const detail = event.target.closest('[data-detail]');
  const review = event.target.closest('[data-review]');
  if (detail) openDetail(detail.dataset.detail);
  if (review) openReview(review.dataset.review);
});
$('#sourceFiles').addEventListener('change', event => {
  const files = [...event.target.files];
  $('#fileNames').textContent = files.length ? files.map(f => `${f.name} (${formatBytes(f.size)})`).join(' · ') : 'No files selected';
});
$('#submissionForm').addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget;
  const files = $('#sourceFiles').files;
  const languages = $$('input[name="languages[]"]:checked');
  if (!files.length) { $('#submissionMessage').textContent = 'Please select at least one source file.'; return; }
  if (!languages.length) { $('#submissionMessage').textContent = 'Choose at least one programming language.'; return; }
  const button = $('#submitProjectBtn'); button.disabled = true; button.textContent = 'Uploading…';
  const data = new FormData();
  data.append('title',$('#projectTitle').value.trim());
  data.append('description',$('#projectDescription').value.trim());
  languages.forEach(x => data.append('languages[]',x.value));
  [...files].forEach(f => data.append('files[]',f));
  try {
    await api('submissions/create.php',{method:'POST',body:data});
    form.reset(); $('#fileNames').textContent = 'No files selected'; $('#submissionMessage').textContent = '';
    $('#submissionDialog').close(); toast('Project submitted for review.');
    await loadWorkspace(); navigate('projects');
  } catch (e) { $('#submissionMessage').textContent = e.message; $('#submissionMessage').className = 'form-message error'; }
  finally { button.disabled = false; button.innerHTML = 'Submit for review <span>→</span>'; }
});
$('#reviewForm').addEventListener('submit', async event => {
  event.preventDefault();
  const button = $('#saveReviewBtn'); button.disabled = true; button.textContent = 'Saving…';
  try {
    await api('reviews/create.php',{method:'POST',body:JSON.stringify({
      submission_id:Number($('#reviewSubmissionId').value),
      status:$('#reviewStatus').value,
      feedback:$('#reviewFeedback').value.trim()
    })});
    $('#reviewDialog').close(); toast('Review saved and developer notified.');
    await loadWorkspace(); navigate('projects');
  } catch (e) { $('#reviewFormMessage').textContent = e.message; $('#reviewFormMessage').className = 'form-message error'; }
  finally { button.disabled = false; button.innerHTML = 'Save review <span>→</span>'; }
});
$$('[data-close]').forEach(button => button.addEventListener('click', () => document.getElementById(button.dataset.close).close()));
$('#mobileMenu').addEventListener('click', () => $('#sidebar').classList.toggle('open'));

async function initialize() {
  try {
    const data = await api('auth/me.php');
    csrfToken = data.csrf_token || '';
    if (data.authenticated) { currentUser = data.user; showApp(); navigate('dashboard'); }
    else showAuth();
  } catch (e) {
    showAuth('Could not connect to the server. Check the database setup and PHP configuration.');
    $('#authMessage').className = 'form-message error';
  }
}
initialize();
