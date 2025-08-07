<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.7/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.7.8/plyr.min.css" />

  <link rel="stylesheet" href="./assets/css/main.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="./assets/css/mobile.css?v=<?php echo time(); ?>" />

  <title>היי מה נשמע?</title>
</head>

<body>

  <div id="login-root" style="display:none;"></div>

  <div id="main" class="main-container row">
    <div id="chats_list" class="left-container col-md-4">
      <div class="header row">
        <div class="col-12 row">
          <div class="user_avatar_container col-2">
            <img src="./profile_pics/assaf.jpg" alt="User's Avatar" />
          </div>
          <div class="user_info_container col-6">
            <div class="user_full_name_comes_here">Assaf Levy</div>
            <div class="user_status_comes_here hide_on_mobile">Online</div>
          </div>
          <div class="logout_btn_container col-4">
            <button class="logout btn btn-dark">Logout</button>
          </div>
        </div>
      </div>
      <div class="search-container">
        <div class="input">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" placeholder="Search or start new chat" />
        </div>
        <i class="fa-sharp fa-solid fa-bars-filter"></i>
      </div>
      <div id="chats" class="chat-list"></div>
    </div>

    <div id="chat_window" class="right-container col-md-8">
      <div class="header row">
        <div class="row col-10">
          <div class="show_chats_list col-2">
            <i class="fa-solid fa-chevron-left"></i>
          </div>
          <div class="contact_profile_img col-3">
            <img class="dp" src="" alt="" />
          </div>
          <div class="contact_name_container col-7">
            <span class="contact_name"></span>
            <span class="contact_id"></span>
          </div>
        </div>
        <div class="contact_more_options col-2">
          <ul class="row">
            <li class="col-6 show_more_option_menu">
              <i class="fa-solid fa-ellipsis-vertical"></i>
            </li>
          </ul>
        </div>
      </div>
      <div id="msgs" class="chat-container"></div>
      <form id="send_msg" class="send_msg_form chatbox-input">
        <i class="fa-sharp fa-solid fa-paperclip"></i>
        <input id="msg" type="text" placeholder="Type a message" required />
        <button class="submit_msg">
          <i class="fa-solid fa-paper-plane"></i>
        </button>
      </form>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.7.8/plyr.min.js"></script>

  <script src="./assets/js/main.js?v=<?php echo time(); ?>"></script>

  <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>


  <script src="https://cdn.jsdelivr.net/npm/picmo@latest/dist/umd/index.js"></script>

  <!-- React Login Component -->
  <script type="text/babel">
    function Login() {
      const [username, setUsername] = React.useState("");
      const [otpCode, setOtpCode] = React.useState("");
      const [message, setMessage] = React.useState("");
      const [honeypot, setHoneypot] = React.useState("");
      const [isLoading, setIsLoading] = React.useState(false);
      const [otpSent, setOtpSent] = React.useState(false);

      const resetLoginStates = React.useCallback(() => {
        setUsername("");
        setOtpCode("");
        setMessage("");
        setHoneypot("");
        setIsLoading(false);
        setOtpSent(false);
      }, []);

      const saveAttempt = () => {
        const now = Date.now();
        let attempts = JSON.parse(localStorage.getItem("otpAttempts") || "[]");

        attempts.push(now);
        attempts = attempts.filter((ts) => now - ts <= 24 * 60 * 60 * 1000);

        localStorage.setItem("otpAttempts", JSON.stringify(attempts));
        return attempts;
      };

      const getAttemptCounts = () => {
        const now = Date.now();
        const attempts = JSON.parse(localStorage.getItem("otpAttempts") || "[]");

        const last30Sec = attempts.filter((ts) => now - ts <= 30 * 1000).length;
        const lastHour = attempts.filter((ts) => now - ts <= 60 * 60 * 1000).length;
        const last1Day = attempts.filter((ts) => now - ts <= 24 * 60 * 60 * 1000).length;;

        return { last30Sec, lastHour, last1Day };
      };

      const sendOtp = () => {
        if (honeypot.trim() !== "") {
          setMessage("בקשה לא חוקית");
          return;
        }

        if (!username.trim()) {
          setMessage("אנא הזיני שם משתמש");
          return;
        }

        const attempts = getAttemptCounts();

        if (attempts.last1Day >= 10) {
          setMessage("הגעת למקסימום 10 קודים ביום");
          return;
        }

        if (attempts.lastHour >= 4) {
          setMessage("הגעת למקסימום 4 קודים בשעה");
          return;
        }

        if (attempts.last30Sec >= 1) {
          setMessage("ניתן לבקש קוד חדש רק כל 30 שניות");
          return;
        }

        setIsLoading(true);
        setMessage("");

        postToServer({
          route: "send_otp",
          data: { 
            username, 
            website: honeypot 
          },
          method: "POST",
          successCallback: (data) => {
            setIsLoading(false);
            if (data) {
              setMessage("קוד נשלח בהצלחה ל-" + username);
              saveAttempt();
              setOtpSent(true);
            } else {
              setMessage("אירעה שגיאה בשליחת הקוד");
            }
          },
          errorCallback: (xhr, status, error) => {
            setIsLoading(false);
            setMessage("שגיאה בשרת, נסי שוב מאוחר יותר");
            console.error(error);
          }
        });
      };

      const verifyOtp = () => {
        if (honeypot.trim() !== "") {
          setMessage("בקשה לא חוקית");
          return;
        }

        if (!username.trim()) {
          setMessage("אנא הזיני שם משתמש");
          return;
        }

        if (!otpCode.trim()) {
          setMessage("אנא הזיני קוד אימות");
          return;
        }

        setIsLoading(true);
        setMessage("");

        postToServer({
          route: "verify_otp",
          data: { 
            username: username.trim(),
            otp: otpCode.trim(),
            website: honeypot 
          },
          method: "POST",
          successCallback: (data) => {
            setIsLoading(false);
            if (data.success) {
              setMessage("התחברת בהצלחה!");
    
              document.getElementById("login-root").style.display = "none";
              document.getElementById("main").style.display = "flex";
              resetLoginStates()
            } else {
              setMessage(data.message || "קוד אימות שגוי");
            }
          },
          errorCallback: (xhr, status, error) => {
            setIsLoading(false);
            setMessage("שגיאה בשרת, נסי שוב מאוחר יותר");
            console.error(error);
          },
        });
      };

      const resetForm = () => {
        setOtpSent(false);
        setOtpCode("");
        setMessage("");
      };

      return (
        <div className="login-container" dir="rtl">
          <div className="login-box">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg"
              alt="logo"
              className="login-logo"
            />
            <h2>התחברות</h2>
            
            {!otpSent ?<>
                <input
                  type="text"
                  placeholder="שם משתמש"
                  className="login-input"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  disabled={isLoading}
                />
                <input
                  type="text"
                  name="website"
                  value={honeypot}
                  onChange={(e) => setHoneypot(e.target.value)}
                  style={{ display: "none" }}
                  autoComplete="off"
                  tabIndex="-1"
                />
                <button
                  className="login-btn"
                  onClick={sendOtp}
                  disabled={isLoading}
                >
                  {isLoading ? "שולח..." : "שלח קוד"}
                </button>
              </> : <>
                <div className="username-display">
                  שם משתמש: <strong>{username}</strong>
                  <button 
                    type="button" 
                    className="btn btn-link btn-sm"
                    onClick={resetForm}
                  >
                    שנה
                  </button>
                </div>
                
                <input
                  type="text"
                  placeholder="הזיני קוד אימות"
                  className="login-input"
                  value={otpCode}
                  onChange={(e) => setOtpCode(e.target.value)}
                  disabled={isLoading}
                  maxLength="10"
                />
                
                <input
                  type="text"
                  name="website"
                  value={honeypot}
                  onChange={(e) => setHoneypot(e.target.value)}
                  style={{ display: "none" }}
                  autoComplete="off"
                  tabIndex="-1"
                />
                
                <button
                  className="login-btn"
                  onClick={verifyOtp}
                  disabled={isLoading}
                >
                  {isLoading ? "מאמת..." : "אמת קוד"}
                </button>
                
                <button
                  className="login-btn-secondary"
                  onClick={sendOtp}
                  disabled={isLoading}
                >
                  שלח קוד חדש
                </button>
              </>}
            
            {message && <p className="login-message">{message}</p>}
          </div>
        </div>
      );
    }

    window.loginRendered = false;

    function renderLogin() {
      const root = ReactDOM.createRoot(document.getElementById("login-root"));
      root.render(<Login />);
      window.loginRendered = true;
    }
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      let picker = null;
      let isPickerOpen = false;

      const emojiButton = document.createElement('i');
      emojiButton.className = 'fa-solid fa-face-smile emoji-btn';

      const form = document.getElementById('send_msg');
      const messageInput = document.getElementById('msg');
      form.insertBefore(emojiButton, messageInput);

      function isSingleEmoji(text) {
        const trimmed = text.trim();
        return trimmed.length >= 1 && trimmed.length <= 2 &&
          /[\u{1f600}-\u{1f64f}]|[\u{1f300}-\u{1f5ff}]|[\u{1f680}-\u{1f6ff}]|[\u{1f1e0}-\u{1f1ff}]|[\u{2600}-\u{26ff}]|[\u{2700}-\u{27bf}]/u.test(trimmed);
      }

      function checkMessages() {
        const messages = document.querySelectorAll('.message-box p');
        messages.forEach(msg => {
          const text = msg.textContent.split('\n')[0];
          if (isSingleEmoji(text)) {
            msg.classList.add('large-emoji');
          }
        });
      }

      const chatContainer = document.getElementById('msgs');
      if (chatContainer) {
        const observer = new MutationObserver(() => {
          setTimeout(checkMessages, 100);
        });
        observer.observe(chatContainer, {
          childList: true,
          subtree: true
        });
      }

      checkMessages();

      emojiButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!picker) {
          const pickerContainer = document.createElement('div');
          pickerContainer.id = 'emoji-picker-container';
          form.appendChild(pickerContainer);

          picker = picmo.createPicker({
            rootElement: pickerContainer,
          });

          picker.addEventListener('emoji:select', (event) => {
            const input = document.getElementById('msg');
            const cursorPos = input.selectionStart || input.value.length;
            const textBefore = input.value.substring(0, cursorPos);
            const textAfter = input.value.substring(cursorPos);

            input.value = textBefore + event.emoji + textAfter;

            const newCursorPos = cursorPos + event.emoji.length;
            input.setSelectionRange(newCursorPos, newCursorPos);
            input.focus();
          });
        }

        const pickerContainer = document.getElementById('emoji-picker-container');
        if (isPickerOpen) {
          pickerContainer.style.display = 'none';
          isPickerOpen = false;
        } else {
          pickerContainer.style.display = 'block';
          isPickerOpen = true;
        }
      });

      document.addEventListener('click', function(e) {
        if (!e.target.closest('#emoji-picker-container') && !e.target.closest('.emoji-btn')) {
          const pickerContainer = document.getElementById('emoji-picker-container');
          if (pickerContainer) {
            pickerContainer.style.display = 'none';
            isPickerOpen = false;
          }
        }
      });
    });
  </script>

</body>

</html>