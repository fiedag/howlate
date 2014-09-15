howlate
=======

how-late.com

This software runs on LAMP cPanel 11.


Procedure for developing:

- D:\Github\Prod\howlate contains the PROD source code and should point to the how-late.com site
- D:\Github\Dev\howlate contains the DEV source code and should point to the fiedlerconsulting.com.au site
- The DEV source code should be source code from a branch which is not master
- Create a branch from master BRANCH_X
- Pull down that branch into the D:\Github\Dev\howlate folder
- Open the IDE and check the Configuration points it to the fiedlerc config
- Begin making changes
- Test and complete the changes.
- Commit those changes to the branch.
- Push those changes to the remote repo of that branch
- You have the option to use the git shell to create a tag 'git tag v1.x' followed by 'git push --tags'
- This should only create a tag for that branch
- Now that these changes are made and tested, create a new Pull Request
- Get a git shell
- Navigate to D:\Github\Prod
- Do the commands listed in the pull request
- 



One article talks about doing "git fetch" followed by "git reset --hard origin/master" to ensure that the local copy is completely brought up to date from master.


