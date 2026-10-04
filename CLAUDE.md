# Repository notes for Claude

WordPress plugins for parafiapio.pl by cruzLabs. Talk to the user in Polish.

- Front-end work on any plugin here (or any other cruzLabs parish plugin):
  follow the skill **`.claude/skills/parafia-frontend/`** – design system,
  Avada / The Events Calendar traps, workflow, test scripts. Read its
  `SKILL.md` first and add new lessons to its log when a round teaches
  something.
- `piodesign/` is the reference implementation (Parafia: PioDesign).
  Edit CSS only in `piodesign/assets/src/piodesign.css`, then run
  `php piodesign/tools/build-css.php`.
- `preview/` renders the plugin's templates offline into one HTML file for an
  artifact preview (`php preview/build.php <out.html>`).
- Zips (`*.zip`) are gitignored; build them for delivery only.
