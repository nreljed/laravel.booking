# HOW DOCKER & UBUNTU IMAGE WORK ON WINDOWS 11

## The Confusion Explained

You're confused because the terms are different. Let me clarify:

---

## WINDOWS 11 vs UBUNTU IMAGE - WHAT'S THE DIFFERENCE?

### Windows 11 (Your Computer's Operating System)
- **What it is:** Your PC runs Windows 11 - a Microsoft operating system
- **What it can do:** Run Windows applications, PowerShell, Command Prompt
- **Can it run Docker containers natively?** NO - Windows can't directly run Linux containers

### Ubuntu Image (A Linux Operating System - Inside Docker)
- **What it is:** A snapshot/blueprint of Ubuntu Linux (a different OS)
- **What it does:** Docker uses this to create a virtual Linux environment
- **Does your computer actually have it?** NO - You download it like a ZIP file
- **Where is it stored?** In Docker (as a 119MB file in your Docker images)

---

## HOW IT WORKS - SIMPLE FLOW

```
YOUR PC (Windows 11)
       ↓
   Docker Desktop (runs via WSL2)
       ↓
   Downloads ubuntu:22.04 image (119MB file)
       ↓
   Creates a Linux container (like a mini virtual Linux machine)
       ↓
   Runs your code in that Linux container
       ↓
   Returns results to your Windows terminal
```

### Real-World Analogy:
```
Imagine:
- Windows 11 = Your house (physical building)
- Docker = A furniture delivery service
- Ubuntu image = A pre-packaged apartment setup

When you ask Docker to run something:
1. Docker gets the Ubuntu image (the apartment setup)
2. Docker creates a temporary container (sets up the apartment)
3. Your code runs inside that container (lives in the apartment)
4. When done, container is destroyed (apartment is disassembled)
```

---

## WHY DO YOU NEED THE UBUNTU IMAGE?

Your workflows need:
- ✅ PHP 8.4
- ✅ Node.js 22
- ✅ Composer
- ✅ npm
- ✅ Git

**Windows 11 doesn't have these pre-installed.** But the Ubuntu image DOES (it comes with Linux package managers that can install them).

So the flow is:
```
act command runs
    ↓
Docker starts ubuntu:22.04 container
    ↓
Ubuntu Linux environment ready
    ↓
apt-get installs PHP, Node, Composer
    ↓
Your tests/builds run
    ↓
Results shown in your Windows terminal
```

---

## WHERE DOES THE UBUNTU IMAGE COME FROM?

**You download it from Docker Hub** (publicly available):

```powershell
# This downloads the image
docker pull ubuntu:22.04

# Result: 119MB file stored in Docker
# Usually at: C:\ProgramData\Docker\containers\

# Then docker uses it to create containers
docker run ubuntu:22.04  # Creates a temporary Linux environment
```

**Is it embedded in Windows 11?** NO
**Do you have to create it?** NO - you just download the blueprint
**Does it take up space?** Yes - 119MB (very small compared to a full OS)

---

## WSL2 CONNECTION

```
Windows 11
    ↓
WSL2 (Windows Subsystem for Linux 2)
    ↓
Provides a Linux kernel to run Linux containers
    ↓
Docker Desktop uses WSL2 to run the Ubuntu image
    ↓
act uses Docker to run your workflows
```

**Why WSL2?** Because it's Microsoft's built-in way to run Linux on Windows efficiently (no separate VM needed).

---

## WHAT ACTUALLY HAPPENS WHEN YOU RUN ACT

**You type:**
```powershell
act pull_request -W ".github\workflows\pr.yml" --secret-file .secrets
```

**Behind the scenes:**

1. **act** (on Windows) reads your workflow file
2. **act** tells Docker: "I need to run this in an Ubuntu environment"
3. **Docker** checks: "Do I have ubuntu:22.04 image?" 
   - If NO: Downloads it (~119MB)
   - If YES: Uses the cached copy
4. **Docker** creates a container from that image (temporary Linux environment)
5. **Inside that container:**
   - PHP 8.4 is installed
   - Node.js 22 is installed
   - Your code runs
   - Tests execute
6. **Docker** collects the results
7. **Docker** destroys the container
8. **Results** displayed in your Windows terminal

---

## SO WHY 119MB FOR UBUNTU?

You might ask: "But Windows 11 is 20GB+, why is Ubuntu only 119MB?"

**Because the ubuntu:22.04 image is NOT a full OS installation:**

```
Full Ubuntu installation:     ~5GB (complete OS files)
ubuntu:22.04 Docker image:    119MB (minimal essentials only)

What's included in the 119MB:
✅ Linux kernel interface
✅ Package manager (apt)
✅ Basic system tools
✅ Standard libraries

What's NOT included:
❌ GUI (desktop interface)
❌ Pre-installed applications (except essentials)
❌ Bloat
```

It's like the difference between:
- **Full OS = A complete house with furniture**
- **Docker image = A blueprint + essential tools to build it quickly**

---

## MULTIPLE CONTAINERS POSSIBLE?

You can run MULTIPLE containers from the SAME ubuntu:22.04 image:

```powershell
# First workflow - Container 1
act pull_request -W ".github\workflows\pr.yml" &

# Second workflow - Container 2
act push -W ".github\workflows\develop-ci.yml" &

# Both use the SAME ubuntu:22.04 image
# But each has its own isolated environment
```

Each container:
- Shares the base image (119MB downloaded once)
- Gets its own temporary filesystem (created from the image)
- Runs isolation (doesn't interfere with others)

---

## IS UBUNTU RUNNING ON MY PC NOW?

**Right now, no.** Ubuntu image is just sitting as a 119MB file in Docker storage.

**When does it run?**
- Only when you execute `act` commands
- Only for as long as needed
- Then it's destroyed

**Is it always consuming resources?** NO
- It's only a file until you use it
- Containers are created and destroyed on demand
- Think of it like: the image is a cookbook, containers are prepared meals

---

## THE COMPLETE PICTURE

```
Windows 11 (Your PC)
├─ Docker Desktop (app that manages containers)
│  ├─ WSL2 (provides Linux kernel support)
│  └─ Container Storage (where images live)
│     └─ ubuntu:22.04 (119MB image file)
│
└─ When you run `act`:
   └─ Docker creates temporary Linux environment from ubuntu:22.04
      └─ PHP, Node, tests run inside
      └─ Results returned to Windows
      └─ Environment destroyed
```

---

## SUMMARY

**Q: Why does Windows 11 need an Ubuntu image?**
A: Windows can't run Linux containers. Docker needs a Linux environment. Ubuntu image provides that.

**Q: Is Ubuntu installed on my PC?**
A: No, just a 119MB blueprint file in Docker.

**Q: Does it run all the time?**
A: No, only when you run `act`.

**Q: Do I need to do anything?**
A: No, it's already downloaded and configured.

**Q: How does act use it?**
A: act → Docker → creates Linux container from ubuntu:22.04 → runs code → destroys container.

---

## YOU'RE ALL SET

✅ Windows 11 = Your PC
✅ Docker Desktop = Container manager
✅ WSL2 = Linux support layer
✅ ubuntu:22.04 = Pre-built Linux environment (119MB)
✅ act = CLI tool that orchestrates everything

Everything works together seamlessly. You just run commands and it handles the rest!

