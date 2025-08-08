# Política de Liberación (Release Policy)

## Versionado
- Semántico (semver): `MAJOR.MINOR.PATCH` (ej. `1.0.1`).
- La versión viva está en `VERSION.txt`.

## Criterios para liberar
- [ ] CI en GitHub Actions pasa (lint y tests si existen).
- [ ] Pull Request hacia `main` con **2 aprobaciones**.
- [ ] QA deja comentario “QA OK vX.Y.Z” en la PR.
- [ ] `README.md` y `CHANGELOG.md` actualizados.
- [ ] `VERSION.txt` actualizado.
- [ ] Tag anotado `vX.Y.Z` creado y Release generado.

## Flujo de release
1. Crear rama `release/x.y.z` desde `main`.
2. Actualizar `VERSION.txt` y `CHANGELOG.md`.
3. Abrir PR → 2 approvals + CI OK.
4. Merge (squash) a `main`.
5. Crear tag `vX.Y.Z` y publicar Release.

## Responsables
- **Líder técnico**: autoriza merge final.
- **QA**: valida y comenta “QA OK vX.Y.Z”.
- **Dev**: mantiene `CHANGELOG.md` y `VERSION.txt`.
