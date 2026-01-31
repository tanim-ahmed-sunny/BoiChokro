<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 

$thread_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT t.*, u.username, b.title as book_title,
        (SELECT COUNT(*) FROM community_appreciations WHERE item_id = t.id AND item_type = 'thread') as appreciation_count,
        (SELECT COUNT(*) FROM community_comments WHERE thread_id = t.id) as comment_count
        FROM community_threads t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN books b ON t.book_id = b.id
        WHERE t.id = $thread_id AND t.status = 'active'";
$threadRes = $conn->query($sql);
$thread = $threadRes ? $threadRes->fetch_assoc() : null;

if (!$thread) {
    echo "<div class='container py-5 text-center'><h4>Discussion not found.</h4><a href='community.php' class='btn btn-primary rounded-pill mt-3'>Back to Community</a></div>";
    require_once 'backend/includes/footer.php';
    exit;
}

$commentsSql = "SELECT c.*, u.username 
               FROM community_comments c 
               JOIN users u ON c.user_id = u.id 
               WHERE c.thread_id = $thread_id AND c.parent_id IS NULL 
               ORDER BY c.created_at ASC";
$comments = $conn->query($commentsSql);

$trendingSql = "SELECT t.id, t.title, 
                (SELECT COUNT(*) FROM community_appreciations WHERE item_id = t.id AND item_type = 'thread') as likes
                FROM community_threads t 
                WHERE t.status = 'active' 
                ORDER BY likes DESC LIMIT 5";
$trendingThreads = $conn->query($trendingSql);

$userPosts = null;
$userComments = null;
if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $userPosts = $conn->query("SELECT id, title FROM community_threads WHERE user_id = $uid AND status = 'active' ORDER BY created_at DESC LIMIT 5");
    $userComments = $conn->query("SELECT c.*, t.title as thread_title FROM community_comments c JOIN community_threads t ON c.thread_id = t.id WHERE c.user_id = $uid ORDER BY c.created_at DESC LIMIT 5");
}

$liked = false;
if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $liked = $conn->query("SELECT id FROM community_appreciations WHERE user_id=$uid AND item_id=$thread_id AND item_type='thread'")->num_rows > 0;
}
?>

<div class="bg-communities pt-4 min-vh-100">
    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-3 col-communities-left d-none d-lg-block">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="community-nav-card shadow-sm mb-4">
                        <div class="community-nav-header">My Discussions</div>
                        <div class="community-nav-list py-2">
                            <?php if ($userPosts && $userPosts->num_rows > 0): ?>
                                <?php while($up = $userPosts->fetch_assoc()): ?>
                                    <a href="thread_view.php?id=<?php echo (int)$up['id']; ?>" class="community-nav-item py-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-chat-left-text text-indigo small"></i>
                                            <span class="text-dark small text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($up['title']); ?></span>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="px-4 py-3 text-muted small">No posts yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="community-nav-card shadow-sm">
                        <div class="community-nav-header">My Comments</div>
                        <div class="community-nav-list py-2">
                            <?php if ($userComments && $userComments->num_rows > 0): ?>
                                <?php while($uc = $userComments->fetch_assoc()): ?>
                                    <a href="thread_view.php?id=<?php echo (int)$uc['thread_id']; ?>" class="community-nav-item py-2 border-bottom last-child-no-border">
                                        <div class="d-flex flex-column">
                                            <small class="text-indigo fw-bold text-truncate" style="max-width: 180px; font-size: 0.7rem;"><?php echo htmlspecialchars($uc['thread_title']); ?></small>
                                            <span class="text-secondary small text-truncate" style="font-size: 0.75rem;"><?php echo htmlspecialchars($uc['content']); ?></span>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="px-4 py-3 text-muted small">No comments yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="community-nav-card shadow-sm text-center p-4">
                        <i class="bi bi-person-circle text-muted display-6 mb-3"></i>
                        <h6 class="fw-bold">Welcome!</h6>
                        <p class="small text-muted mb-3">Login to see your activity and join the discussion.</p>
                        <a href="login.php" class="btn btn-primary btn-sm rounded-pill w-100">Login Now</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-6">
                <div class="d-lg-none mb-4">
                    <a href="community.php" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-arrow-left me-2"></i>Back to Feed
                    </a>
                </div>

                <nav aria-label="breadcrumb" class="mb-4 d-none d-lg-block">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="community.php" class="text-decoration-none text-muted small">Community</a></li>
                        <li class="breadcrumb-item active text-dark fw-bold small" aria-current="page">Discussion Details</li>
                    </ol>
                </nav>

                <div class="community-card shadow-sm fadeIn rounded-4 mb-4">
                    <div class="px-4 pt-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="bg-emerald-50 text-emerald-600 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small">@<?php echo htmlspecialchars($thread['username']); ?></div>
                            </div>
                        </div>
                        <h2 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($thread['title']); ?></h2>
                        <div class="text-secondary mb-4" style="line-height: 1.7; font-size: 1.05rem;">
                            <?php echo nl2br(htmlspecialchars($thread['content'])); ?>
                        </div>
                        <?php if(!empty($thread['image_url'])): ?>
                            <div class="rounded-4 overflow-hidden mb-4 shadow-sm">
                                <img src="<?php echo htmlspecialchars($thread['image_url']); ?>" class="w-100 object-fit-cover" alt="Thread Image">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="thread-meta border-top-0 bg-white px-4 pb-3">
                        <div class="d-flex align-items-center gap-4">
                            <button class="meta-btn appreciation-btn <?php echo $liked ? 'active' : ''; ?>" data-id="<?php echo (int)$thread['id']; ?>" data-type="thread">
                                <i class="bi <?php echo $liked ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                <span class="count"><?php echo (int)$thread['appreciation_count']; ?></span>
                            </button>
                            <span class="meta-btn">
                                <i class="bi bi-chat-dots"></i>
                                <span><?php echo (int)$thread['comment_count']; ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="comments-container community-card shadow-sm rounded-4 overflow-hidden mb-5">
                    <div class="px-4 py-3 border-bottom bg-light bg-opacity-10 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-calm mb-0">Comments (<?php echo (int)$thread['comment_count']; ?>)</h5>
                    </div>
                    <div class="comments-list p-0">
                        <?php if ($comments && $comments->num_rows > 0): ?>
                            <?php while($comment = $comments->fetch_assoc()): 
                                $pId = (int) $comment['id'];
                                $repliesSql = "SELECT c.*, u.username FROM community_comments c JOIN users u ON c.user_id = u.id WHERE c.parent_id = $pId ORDER BY c.created_at ASC";
                                $replies = $conn->query($repliesSql);
                            ?>
                                <div class="comment-item px-4 py-3 border-bottom last-child-no-border">
                                    <div class="d-flex align-items-center gap-2 mb-2 small">
                                        <span class="fw-bold text-calm">@<?php echo htmlspecialchars($comment['username']); ?></span>
                                    </div>
                                    <div class="text-secondary mb-3" style="font-size: 0.95rem;">
                                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                    </div>
                                    <?php if(!empty($comment['image_url'])): ?>
                                        <div class="mb-3">
                                            <img src="<?php echo htmlspecialchars($comment['image_url']); ?>" class="rounded-3 shadow-sm" style="max-width: 100%; max-height: 300px;" alt="Comment Photo">
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex gap-2">
                                        <button class="appreciation-btn py-1 px-3 border" style="font-size: 0.8rem;" data-id="<?php echo (int)$comment['id']; ?>" data-type="comment">
                                            <i class="bi bi-heart"></i> Appreciate
                                        </button>
                                        <?php if(isset($_SESSION['user_id'])): ?>
                                            <button class="appreciation-btn py-1 px-3 border" style="font-size: 0.8rem;" onclick="showReplyForm(<?php echo (int)$comment['id']; ?>)">
                                                Reply
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div id="reply-form-<?php echo (int)$comment['id']; ?>" class="mt-3 d-none">
                                        <form action="backend/actions/community_actions.php" method="POST">
                                            <input type="hidden" name="action" value="post_comment">
                                            <input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
                                            <input type="hidden" name="parent_id" value="<?php echo (int)$comment['id']; ?>">
                                            <input type="hidden" name="redirect_to" value="thread_view.php?id=<?php echo $thread_id; ?>#reply-form-<?php echo (int)$comment['id']; ?>">
                                            <textarea name="content" class="form-control form-control-sm bg-white border-0 shadow-sm mb-2" rows="2" placeholder="Write a reply..."></textarea>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Post Reply</button>
                                                <button type="button" class="btn btn-light btn-sm rounded-pill px-3" onclick="hideReplyForm(<?php echo (int)$comment['id']; ?>)">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                    <?php if ($replies && $replies->num_rows > 0): ?>
                                        <div class="replies-wrapper mt-3 ps-3 border-start">
                                            <?php while($reply = $replies->fetch_assoc()): ?>
                                                <div class="reply-item py-2">
                                                    <div class="d-flex align-items-center gap-2 mb-1 small">
                                                        <span class="fw-bold text-calm">@<?php echo htmlspecialchars($reply['username']); ?></span>
                                                    </div>
                                                    <div class="text-secondary small">
                                                        <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                                                    </div>
                                                    <div class="mt-2 d-flex gap-2">
                                                        <button class="appreciation-btn py-0 px-2 border-0 bg-transparent" style="font-size: 0.75rem;" data-id="<?php echo (int)$reply['id']; ?>" data-type="comment">
                                                            <i class="bi bi-heart"></i> Appreciate
                                                        </button>
                                                        <?php if(isset($_SESSION['user_id'])): 
                                                            $mention = '@' . $reply['username'] . ' ';
                                                        ?>
                                                            <button class="appreciation-btn py-0 px-2 border-0 bg-transparent" style="font-size: 0.75rem;" onclick="showReplyForm(<?php echo (int)$comment['id']; ?>, <?php echo json_encode($mention); ?>)">
                                                                Reply
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-chat-dots text-muted opacity-25 display-6 d-block mb-3"></i>
                                <p class="text-muted mb-0 small">No comments yet. Start the conversation!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="px-4 py-4 bg-light bg-opacity-10 border-top">
                        <h6 class="fw-bold text-calm mb-3">Add Your Comment</h6>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form action="backend/actions/community_actions.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="post_comment">
                                <input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
                                <div class="mb-3">
                                    <textarea name="content" class="form-control bg-white border-0 shadow-sm" rows="3" required placeholder="What are your thoughts?"></textarea>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="btn btn-light btn-sm rounded-pill text-muted px-3 border-0 shadow-sm" style="cursor: pointer;">
                                            <i class="bi bi-image me-1"></i>Add Photo
                                            <input type="file" name="image" class="d-none" accept="image/*" onchange="previewImage(this)">
                                        </label>
                                        <div id="imagePreview" class="small text-muted"></div>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Post Comment</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-light border-0 shadow-sm rounded-4 p-3 text-center mb-0">
                                <p class="mb-2 small">Please login to join the discussion.</p>
                                <a href="login.php" class="btn btn-primary btn-sm rounded-pill px-4">Login</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-communities-right">
                <div class="mb-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="community.php" class="btn btn-primary w-100 py-2 shadow-sm mb-4 d-none d-lg-block">
                        <i class="bi bi-arrow-left me-2"></i>Back to Community
                    </a>
                    <?php endif; ?>
                    <div class="community-nav-card shadow-sm">
                        <div class="community-nav-header">Trending Discussions</div>
                        <div class="community-nav-list py-2">
                            <?php if($trendingThreads && $trendingThreads->num_rows > 0): ?>
                                <?php while($trend = $trendingThreads->fetch_assoc()): ?>
                                    <a href="thread_view.php?id=<?php echo (int)$trend['id']; ?>" class="community-nav-item py-2">
                                        <div class="d-flex flex-column">
                                            <span class="text-dark fw-medium text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($trend['title']); ?></span>
                                            <small class="text-muted"><?php echo (int)$trend['likes']; ?> appreciations</small>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="px-4 py-3 text-muted small">No trending posts yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showReplyForm(id, mention) {
    if (typeof mention === 'undefined') mention = '';
    const form = document.getElementById('reply-form-'+id);
    form.classList.remove('d-none');
    if(mention) {
        const textarea = form.querySelector('textarea');
        if (textarea) { textarea.value = mention; textarea.focus(); }
    }
}
function hideReplyForm(id) {
    document.getElementById('reply-form-'+id).classList.add('d-none');
}
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        preview.textContent = "Selected: " + input.files[0].name;
    }
}
document.querySelectorAll('.appreciation-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if(!this.dataset.id) return;
        const itemId = this.dataset.id;
        const itemType = this.dataset.type;
        const button = this;
        const countSpan = button.querySelector('.count');
        fetch('backend/actions/community_actions.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'toggle_appreciate',
                item_id: itemId,
                item_type: itemType
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success && countSpan) {
                const icon = button.querySelector('i');
                let count = parseInt(countSpan.textContent) || 0;
                if(data.status === 'added') {
                    icon.className = 'bi bi-heart-fill';
                    button.classList.add('active');
                    count++;
                } else {
                    icon.className = 'bi bi-heart';
                    button.classList.remove('active');
                    count--;
                }
                countSpan.textContent = count;
            } else if(data.error === 'Login required') {
                window.location.href = 'login.php';
            }
        });
    });
});
</script>

<?php require_once 'backend/includes/footer.php'; ?>
