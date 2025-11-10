const chatMessages = document.getElementById('chat-messages');
chatMessages.scrollTop = chatMessages.scrollHeight;
        
        document.querySelectorAll('.reply-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            const msgId = this.getAttribute('data-msgid');
            const msgElement = document.getElementById('msg-' + msgId);
            const username = msgElement.querySelector('.username').textContent;
            const message = msgElement.querySelector('.message-content').textContent.trim().substring(0, 50);
            
            document.getElementById('reply_to').value = msgId;
            document.getElementById('reply-preview').innerHTML = `
                    <i class="fa fa-reply"></i>
                    <span class="reply-user">${username}</span>
                    <span class="reply-text">${message}</span>
                    <button type="button" class="cancel-reply"><i class="fa fa-times"></i></button>
                `;
            
            document.querySelector('.cancel-reply').addEventListener('click', function() {
              document.getElementById('reply_to').value = '';
              document.getElementById('reply-preview').innerHTML = '';
            });
          });
        });
        
        setInterval(function() {
          fetch('get_messages.php')
            .then(response => response.text())
            .then(data => {
              const oldScroll = chatMessages.scrollTop;
              const oldHeight = chatMessages.scrollHeight;
              
              chatMessages.innerHTML = data;
              
              if (chatMessages.scrollTop + chatMessages.clientHeight >= oldHeight - 50) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
              } else {
                chatMessages.scrollTop = oldScroll;
              }
            });
        }, 3000);
        
        document.getElementById('avatar_upload').addEventListener('change', function() {
          if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
              document.querySelector('.profile-pic').src = e.target.result;
            }
            reader.readAsDataURL(this.files[0]);
            document.querySelector('.avatar-form').submit();
          }
});