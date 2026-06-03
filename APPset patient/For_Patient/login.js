import React, { useState, useEffect } from "react";

const Login = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [showPassword, setShowPassword] = useState(false);

  const [loginAttempts, setLoginAttempts] = useState(
    Number(localStorage.getItem("attempts")) || 0
  );

  const [lockTime, setLockTime] = useState(
    Number(localStorage.getItem("lockTime")) || 0
  );

  const cooldown = 30;
  const [remainingTime, setRemainingTime] = useState(0);

  // 🔥 CHECK LOCK STATUS
  useEffect(() => {
    if (loginAttempts >= 3) {
      const elapsed = Math.floor(Date.now() / 1000) - lockTime;
      const remaining = cooldown - elapsed;

      if (remaining > 0) {
        setRemainingTime(remaining);
        setError(`Too many failed attempts. Try again in ${remaining} seconds.`);
      } else {
        setLoginAttempts(0);
        setLockTime(0);
        localStorage.setItem("attempts", 0);
        localStorage.setItem("lockTime", 0);
      }
    }
  }, []);

  // 🔥 COUNTDOWN
  useEffect(() => {
    if (remainingTime > 0) {
      const timer = setInterval(() => {
        setRemainingTime((prev) => {
          if (prev <= 1) {
            clearInterval(timer);
            setError("You can try logging in again.");
            setLoginAttempts(0);
            localStorage.setItem("attempts", 0);
            return 0;
          }
          return prev - 1;
        });
      }, 1000);

      return () => clearInterval(timer);
    }
  }, [remainingTime]);

  // 🔥 LOGIN FUNCTION
  const handleLogin = async (e) => {
    e.preventDefault();

    if (remainingTime > 0) return;

    try {
      const res = await fetch("http://localhost/api/login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, password }),
      });

      const data = await res.json();

      if (data.success) {
        localStorage.removeItem("attempts");
        localStorage.removeItem("lockTime");

        // redirect based on role
        window.location.href = data.redirect;
      } else {
        setError(data.message);

        if (data.message === "Incorrect password!") {
          const newAttempts = loginAttempts + 1;
          setLoginAttempts(newAttempts);
          localStorage.setItem("attempts", newAttempts);

          if (newAttempts >= 3) {
            const now = Math.floor(Date.now() / 1000);
            setLockTime(now);
            localStorage.setItem("lockTime", now);
            setRemainingTime(cooldown);
          }
        }
      }
    } catch (err) {
      setError("Server error!");
    }
  };

  return (
    <div className="login-card">
      <div className="logo-container">
        <img src="/logo.jpg" alt="logo" />
      </div>

      <form onSubmit={handleLogin}>
        <div className="input-group">
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>

        <div className="input-group" style={{ position: "relative" }}>
          <input
            type={showPassword ? "text" : "password"}
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <span
            onClick={() => setShowPassword(!showPassword)}
            style={{
              position: "absolute",
              right: "20px",
              top: "50%",
              transform: "translateY(-50%)",
              cursor: "pointer",
            }}
          >
            {showPassword ? "🙈" : "👁"}
          </span>
        </div>

        {error && (
          <div style={{ color: "red", fontWeight: "bold" }}>{error}</div>
        )}

        <button type="submit" disabled={remainingTime > 0}>
          Log In
        </button>
      </form>
    </div>
  );
};

export default Login;