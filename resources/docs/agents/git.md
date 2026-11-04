# Git and GitHub

## Commits

Commit titles follow `{gitmoji} {type}({module}): {description}`, for example
`✨ feat(tasks): add task filters`. Do not put AI attribution in commit
messages. Keep documentation updates in the same commit as the change that
requires them.

| Gitmoji | Type       | Use for              |
| ------- | ---------- | -------------------- |
| ✨      | `feat`     | New features         |
| 🐛      | `fix`      | Bug fixes            |
| 🚑️      | `hotfix`   | Critical hotfixes    |
| 💥      | `breaking` | Breaking changes     |
| ⚡️      | `perf`     | Performance          |
| ♻️      | `refactor` | Refactoring          |
| 📝      | `docs`     | Documentation        |
| 💄      | `ui`       | UI and styles        |
| ✅      | `test`     | Tests                |
| 🔧      | `config`   | Configuration files  |
| 👷      | `ci`       | CI build system      |
| 🔒️      | `security` | Security fixes       |
| 🚀      | `deploy`   | Deployments          |
| ⬆️      | `deps`     | Upgrade dependencies |
| ➕      | `deps`     | Add dependencies     |
| ➖      | `deps`     | Remove dependencies  |
| 🚚      | `move`     | Move or rename files |
| 🔥      | `remove`   | Remove code or files |

## GitHub issues and pull requests

The repository is `kytel0925/laravel-one-shot` on GitHub and its default
branch is `master`. Use the `gh` CLI or the GitHub web UI. Follow the
templates under `.github/` when the repository has them.

1. Confirm the target repository and that `gh auth status` shows an
   authenticated account.
2. Resolve the assignee from `git config user.email` and `git config
   user.name`: check whether `gh api user` reports the same email, otherwise
   find the exact developer among the repository's collaborators
   (`gh api repos/OWNER/REPO/assignees`). Never guess from a similar name. If
   the match is ambiguous, ask.
3. Assign both the issue and its pull request to that developer:

    ```sh
    gh issue create --repo OWNER/REPO --assignee USERNAME --title "..." --body "..."
    gh pr create --repo OWNER/REPO --assignee USERNAME --head BRANCH --base master --title "..." --body "..."
    ```

4. Put `Closes #N` in the pull request description when it should close the
   issue.

GitHub has no time-tracking quick actions, so record estimates and time spent
in the issue text only when the task asks for them.

Do not push branches while creating issues or pull requests unless asked.
