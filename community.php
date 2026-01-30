<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 

$sql = "SELECT t.*, u.username, 
        (SELECT COUNT(*) FROM community_appreciations WHERE item_id = t.id AND item_type = 'thread') as appreciation_count,
        (SELECT COUNT(*) FROM community_comments WHERE thread_id = t.id) as comment_count,
        b.title as book_title
        FROM community_threads t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN books b ON t.book_id = b.id
        WHERE t.status = 'active' 
        ORDER BY (appreciation_count + comment_count) DESC, t.created_at DESC";
$threads = $conn->query($sql);

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
?>

<div class="bg-communities pt-4">
    <div class="container">
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
                    <button class="btn btn-primary w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#startThreadModal">
                        <i class="bi bi-pencil-square me-2"></i>Start a Discussion
                    </button>
                </div>

                <div class="thread-feed">
                    <?php if ($threads && $threads->num_rows > 0): ?>
                        <?php while($row = $threads->fetch_assoc()): 
                            $liked = false;
                            if (isset($_SESSION['user_id'])) {
                                $tid = (int) $row['id'];
                                $uid = (int) $_SESSION['user_id'];
                                $liked = $conn->query("SELECT id FROM community_appreciations WHERE user_id=$uid AND item_id=$tid AND item_type='thread'")->num_rows > 0;
                            }
                        ?>
                            <div id="thread-<?php echo (int)$row['id']; ?>" class="community-card shadow-sm fadeIn rounded-4 pb-0 mb-4">
                                <div class="px-4 pt-4">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="bg-emerald-50 text-emerald-600 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">@<?php echo htmlspecialchars($row['username']); ?></div>
                                        </div>
                                    </div>
                                    
                                    <a href="thread_view.php?id=<?php echo (int)$row['id']; ?>" class="text-decoration-none">
                                        <h4 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($row['title']); ?></h4>
                                        <p class="text-secondary mb-3" style="font-size: 0.95rem; line-height: 1.6;">
                                            <?php 
                                                $content = htmlspecialchars($row['content']);
                                                echo strlen($content) > 250 ? substr($content, 0, 250) . '...' : $content; 
                                            ?>
                                        </p>
                                    </a>

                                    <?php if(!empty($row['image_url'])): ?>
                                        <div class="rounded-4 overflow-hidden mb-3 shadow-sm" style="max-height: 400px;">
                                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="w-100 h-100 object-fit-cover" alt="Discussion">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="thread-meta border-top-0 bg-white px-4 pb-1">
                                    <div class="d-flex align-items-center gap-4">
                                        <button class="meta-btn appreciation-btn p-0 <?php echo $liked ? 'active' : ''; ?>" data-id="<?php echo (int)$row['id']; ?>" data-type="thread">
                                            <i class="bi <?php echo $liked ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                            <span class="count fw-medium"><?php echo (int)$row['appreciation_count']; ?></span>
                                        </button>
                                        <button class="meta-btn p-0 text-decoration-none" onclick="toggleInlineComment(<?php echo (int)$row['id']; ?>)">
                                            <i class="bi bi-chat-dots"></i>
                                            <span class="fw-medium"><?php echo (int)$row['comment_count']; ?></span>
                                        </button>
                                    </div>
                                </div>

                                <div id="inline-comment-<?php echo (int)$row['id']; ?>" class="px-4 pb-4 d-none">
                                    <hr class="my-3 opacity-10">
                                    <div class="previous-comments mb-3">
                                        <?php 
                                        $threadId = (int) $row['id'];
                                        $commentsRes = $conn->query("SELECT c.*, u.username FROM community_comments c JOIN users u ON c.user_id = u.id WHERE c.thread_id = $threadId AND c.parent_id IS NULL ORDER BY c.created_at ASC LIMIT 3");
                                        if ($commentsRes && $commentsRes->num_rows > 0):
                                            while($c = $commentsRes->fetch_assoc()): ?>
                                                <div class="d-flex gap-2 mb-2">
                                                    <div class="bg-light text-indigo rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.7rem;">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                    <div class="bg-light rounded-4 px-3 py-2 flex-grow-1" style="font-size: 0.85rem;">
                                                        <span class="fw-bold d-block" style="font-size: 0.8rem;">@<?php echo htmlspecialchars($c['username']); ?></span>
                                                        <span class="text-secondary"><?php echo htmlspecialchars($c['content']); ?></span>
                                                    </div>
                                                </div>
                                            <?php endwhile;
                                            if ($row['comment_count'] > 3): ?>
                                                <a href="thread_view.php?id=<?php echo (int)$row['id']; ?>" class="text-indigo text-decoration-none small fw-bold ps-4">View all <?php echo (int)$row['comment_count']; ?> comments</a>
                                            <?php endif;
                                        endif; ?>
                                    </div>

                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <form action="backend/actions/community_actions.php" method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="action" value="post_comment">
                                            <input type="hidden" name="thread_id" value="<?php echo (int)$row['id']; ?>">
                                            <input type="hidden" name="redirect_to" value="community.php#thread-<?php echo (int)$row['id']; ?>">
                                            <div class="bg-emerald-50 text-emerald-600 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                                                <i class="bi bi-person-fill small"></i>
                                            </div>
                                            <div class="flex-grow-1 position-relative">
                                                <input type="text" name="content" class="form-control form-control-sm rounded-pill bg-light border-0 px-3" placeholder="Write a comment..." required>
                                                <button type="submit" class="btn btn-link text-indigo p-0 position-absolute end-0 top-50 translate-middle-y me-3">
                                                    <i class="bi bi-send-fill"></i>
                                                </button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="small text-muted text-center py-1">
                                            Please <a href="login.php" class="text-indigo text-decoration-none fw-bold">Login</a> to comment.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="community-empty-state shadow-sm">
                            <div class="mb-4">
                                <i class="bi bi-book text-muted opacity-25" style="font-size: 5rem;"></i>
                            </div>
                            <h3 class="fw-bold text-dark">No posts yet!</h3>
                            <p class="text-secondary mb-4">Be the first to share something with the community.</p>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startThreadModal">Start Discussion</button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">Login to Post</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-3 col-communities-right">
                <div class="mb-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <button class="btn btn-primary w-100 py-2 shadow-sm mb-4 d-none d-lg-block" data-bs-toggle="modal" data-bs-target="#startThreadModal">
                        <i class="bi bi-plus-lg me-2"></i>Create Post
                    </button>
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

<div class="modal fade" id="startThreadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-calm mb-0">Start a Discussion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="backend/actions/community_actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="start_thread">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Topic Title</label>
                        <input type="text" name="title" class="form-control bg-light border-0" required placeholder="What's on your mind?">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Content</label>
                        <textarea name="content" class="form-control bg-light border-0" rows="5" required placeholder="Share your thoughts..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Add a Photo (Optional)</label>
                        <input type="file" name="image" class="form-control bg-light border-0" accept="image/*">
                        <small class="text-muted">Max 3MB. JPG, PNG, WebP.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Share with Community</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleInlineComment(id) {
    const el = document.getElementById('inline-comment-' + id);
    el.classList.toggle('d-none');
    if (!el.classList.contains('d-none')) {
        const input = el.querySelector('input[name="content"]');
        if (input) input.focus();
    }
}

document.querySelectorAll('.appreciation-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if(!this.dataset.id) return;
        const itemId = this.dataset.id;
        const itemType = this.dataset.type;
        const button = this;
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
            if(data.success) {
                const icon = button.querySelector('i');
                const countSpan = button.querySelector('.count');
                if (!countSpan) return;
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
