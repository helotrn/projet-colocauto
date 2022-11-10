# https://waxzce.medium.com/use-bashrc-d-directory-instead-of-bloated-bashrc-50204d5389ff

alias dce='docker-compose exec'
alias dce-test='dce -e DB_DATABASE=locomotion_test' 
alias dcep='dce php' # execute in the php container
alias dar='dce -e XDEBUG_MODE=off php php artisan' # Disable debugging for artisan commands by default

alias locowipe='dce db:wipe'
alias locomigr='dar migrate'
alias locomigr-test='dce-test -e XDEBUG_MODE=off php php artisan migrate'
alias locoseed='dar migrate:fresh --seed'

alias locotest='locomigr-test && dcep-test php ./vendor/bin/phpunit'
alias locotest-detail='locomigr-test && dce-test php php artisan test'

alias locotest-cover='locomigr-test && dce-test -e XDBEBUG_MODE=coverage php ./vendor/bin/phpunit -d memory_limit=-1 --coverage-html=coverage-html'

alias locojest='dce vue npm run test'
alias locojest-cover='dce vue npm run test -- --coverage --collectCoverageFrom="./src/**'
alias locopretty='dce php npx prettier --write .'


alias locogit="xdg-open https://gitlab.com/solon-collectif/locomotion.app/"
alias locogit-issues="xdg-open https://gitlab.com/solon-collectif/locomotion.app/-/issues"
alias locogit-newissue="xdg-open https://gitlab.com/solon-collectif/locomotion.app/-/issues/new"
alias locogit-mr="xdg-open https://gitlab.com/solon-collectif/locomotion.app/-/merge_requests"
alias locogit-tags="xdg-open https://gitlab.com/solon-collectif/locomotion.app/-/tags"

