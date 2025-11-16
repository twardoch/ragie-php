# Documentation Workflow

We use [Zensical](https://pypi.org/project/zensical/) for the static site.

## Layout
- `src-docs/` – Markdown sources, grouped by topic.
- `docs/` – Built HTML + assets that GitHub Pages serves from the default branch.
- `zensical.toml` – Project configuration (nav, theme, markdown extensions).

## Local commands
```bash
uvx zensical serve               # hot-reload preview at http://127.0.0.1:8000
uvx zensical build --clean       # rebuild docs/ from scratch
```

## GitHub Actions
`.github/workflows/docs.yml`:
1. Installs uv via the official installer script.
2. Runs `uv tool run zensical build --clean`.
3. Uploads the `docs/` folder as a Pages artifact and deploys it.

## Contribution checklist
- Keep navigation deterministic by editing `zensical.toml` instead of relying on filesystem order.
- Keep Markdown short and decisive; use admonitions for warnings/notes.
- Run `uvx zensical build --clean` before opening a PR so `docs/` stays in sync with `src-docs/`.
